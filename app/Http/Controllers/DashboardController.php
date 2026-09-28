<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\MaterialMovement;
use App\Models\Supervisor;
use App\Models\Tool;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getDashboardData();

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('dashboard.index', $data);
    }

    public function apiSnapshot()
    {
        return response()->json($this->getDashboardData());
    }

    /**
     * Get aggregated dashboard analytics, vehicle stages, stock alerts, and tool metrics.
     *
     * @return array{
     *     totalActiveVehicles: int,
     *     stuckVehicles: Collection<int, Vehicle>,
     *     lowStockMaterials: Collection<int, Material>,
     *     lowStockSafetyMaterials: Collection<int, Material>,
     *     totalStoreUnitsNeeded: float,
     *     totalSafetyUnitsNeeded: float,
     *     totalStockValue?: float,
     *     monthlyRestockSpend?: float,
     *     monthlyRestocksAwaitingAmount?: int,
     *     stages: array<int, string>,
     *     pipelineCounts: array<string, int>,
     *     maxPipelineCount: int,
     *     toolsSummary: array{total: int, available: int, checked_out: int, calibration_overdue: int},
     *     recentActivities: \Illuminate\Database\Eloquent\Collection<int, ActivityLog>,
     *     totalSupervisors: int
     * }
     */
    private function getDashboardData(): array
    {
        $now = Carbon::now();
        $stages = Qs::getStages();
        $user = Auth::user();

        return array_merge(
            $this->getVehicleMetrics(),
            $this->getMaterialStockAlerts(),
            $user->canViewFinancialSnapshot() ? $this->getFinancialSnapshot($now) : [],
            $this->getPipelineMetrics($stages),
            [
                'toolsSummary' => $this->getToolsSummary($now),
                'recentActivities' => $user->canViewAuditTrail() ? ActivityLog::orderByDesc('id')->take(10)->get() : collect(),
                'totalSupervisors' => Supervisor::count(),
            ]
        );
    }

    /**
     * @return array{totalActiveVehicles: int, stuckVehicles: Collection<int, Vehicle>}
     */
    private function getVehicleMetrics(): array
    {
        $activeVehicles = Vehicle::with(['stageHistories', 'parts'])
            ->where('stage', '!=', '8. Completed & Dispatched')
            ->get();

        $stuckVehicles = $activeVehicles->filter(fn (Vehicle $v) => $v->isStuck())
            ->sortByDesc('days_in_current_stage')
            ->values();

        return [
            'totalActiveVehicles' => $activeVehicles->count(),
            'stuckVehicles' => $stuckVehicles,
        ];
    }

    /**
     * @return array{
     *     lowStockMaterials: Collection<int, Material>,
     *     lowStockSafetyMaterials: Collection<int, Material>,
     *     totalStoreUnitsNeeded: float,
     *     totalSafetyUnitsNeeded: float
     * }
     */
    private function getMaterialStockAlerts(): array
    {
        $allLowStock = Material::all()->filter(fn (Material $m) => $m->isLowStock())->values();

        $lowStockSafetyMaterials = $allLowStock->filter(function (Material $m) {
            return $m->category === 'Worker Safety & PPE'
                || $m->category === 'Reflecting & Safety'
                || stripos($m->name, 'safety') !== false
                || stripos($m->name, 'ppe') !== false
                || stripos($m->name, 'glove') !== false
                || stripos($m->name, 'boot') !== false
                || stripos($m->name, 'helmet') !== false
                || stripos($m->name, 'goggle') !== false
                || stripos($m->name, 'respirator') !== false
                || stripos($m->name, 'mask') !== false;
        })->values();

        $lowStockMaterials = $allLowStock->reject(function (Material $m) use ($lowStockSafetyMaterials) {
            return $lowStockSafetyMaterials->pluck('id')->contains($m->id);
        })->values();

        $totalStoreUnitsNeeded = (float) $lowStockMaterials->sum(fn (Material $m) => max(0, (float) $m->low_stock - (float) $m->qty));
        $totalSafetyUnitsNeeded = (float) $lowStockSafetyMaterials->sum(fn (Material $m) => max(0, (float) $m->low_stock - (float) $m->qty));

        return [
            'lowStockMaterials' => $lowStockMaterials,
            'lowStockSafetyMaterials' => $lowStockSafetyMaterials,
            'totalStoreUnitsNeeded' => $totalStoreUnitsNeeded,
            'totalSafetyUnitsNeeded' => $totalSafetyUnitsNeeded,
        ];
    }

    /**
     * Store inventory value and money spent on supplier restocks delivered
     * this month, as recorded by Accountants.
     *
     * @return array{totalStockValue: float, monthlyRestockSpend: float, monthlyRestocksAwaitingAmount: int}
     */
    private function getFinancialSnapshot(Carbon $now): array
    {
        $totalStockValue = (float) Material::all()->reject(fn (Material $m) => $m->isSafetyStock())->sum(fn (Material $m) => $m->totalValue());

        $mtdRestocks = MaterialMovement::where('type', 'in')
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->get(['amount_spent']);

        return [
            'totalStockValue' => $totalStockValue,
            'monthlyRestockSpend' => (float) $mtdRestocks->sum('amount_spent'),
            'monthlyRestocksAwaitingAmount' => $mtdRestocks->whereNull('amount_spent')->count(),
        ];
    }

    /**
     * @param  array<int, string>  $stages
     * @return array{stages: array<int, string>, pipelineCounts: array<string, int>, maxPipelineCount: int}
     */
    private function getPipelineMetrics(array $stages): array
    {
        $pipelineCounts = [];
        foreach ($stages as $stage) {
            $pipelineCounts[$stage] = Vehicle::where('stage', $stage)->count();
        }

        $maxPipelineCount = max(array_values($pipelineCounts) ?: [1]);

        return [
            'stages' => $stages,
            'pipelineCounts' => $pipelineCounts,
            'maxPipelineCount' => $maxPipelineCount > 0 ? $maxPipelineCount : 1,
        ];
    }

    /**
     * @return array{total: int, available: int, checked_out: int, calibration_overdue: int}
     */
    private function getToolsSummary(Carbon $now): array
    {
        return [
            'total' => Tool::count(),
            'available' => Tool::where('status', 'Available')->count(),
            'checked_out' => Tool::where('status', 'Checked Out')->count(),
            'calibration_overdue' => Tool::whereNotNull('next_calibration')->where('next_calibration', '<', $now->toDateString())->count(),
        ];
    }
}
