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
     *     totalStockValue: float,
     *     monthlyStockIssuedValue: float,
     *     monthlyStockRestockedValue: float,
     *     monthlyNetStockValuationChange: float,
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

        return array_merge(
            $this->getVehicleMetrics(),
            $this->getMaterialStockAlerts(),
            $this->getStockValuationMetrics($now),
            $this->getPipelineMetrics($stages),
            [
                'toolsSummary' => $this->getToolsSummary($now),
                'recentActivities' => ActivityLog::orderByDesc('id')->take(10)->get(),
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
     *     totalSafetyUnitsNeeded: float,
     *     totalStockValue: float
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

        $totalStockValue = (float) Material::all()->reject(fn (Material $m) => $m->isSafetyStock())->sum(fn (Material $m) => $m->totalValue());

        return [
            'lowStockMaterials' => $lowStockMaterials,
            'lowStockSafetyMaterials' => $lowStockSafetyMaterials,
            'totalStoreUnitsNeeded' => $totalStoreUnitsNeeded,
            'totalSafetyUnitsNeeded' => $totalSafetyUnitsNeeded,
            'totalStockValue' => $totalStockValue,
        ];
    }

    /**
     * @return array{
     *     monthlyStockIssuedValue: float,
     *     monthlyStockRestockedValue: float,
     *     monthlyNetStockValuationChange: float
     * }
     */
    private function getStockValuationMetrics(Carbon $now): array
    {
        $mtdMovements = MaterialMovement::with('material')
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->get()
            ->reject(fn (MaterialMovement $m) => $m->material && $m->material->isSafetyStock());

        $monthlyStockIssuedValue = (float) $mtdMovements
            ->where('type', 'out')
            ->sum(fn (MaterialMovement $m) => (float) $m->qty * (float) ($m->material->unit_cost ?? 0));

        $monthlyStockRestockedValue = (float) $mtdMovements
            ->where('type', 'in')
            ->sum(fn (MaterialMovement $m) => (float) $m->qty * (float) ($m->material->unit_cost ?? 0));

        return [
            'monthlyStockIssuedValue' => $monthlyStockIssuedValue,
            'monthlyStockRestockedValue' => $monthlyStockRestockedValue,
            'monthlyNetStockValuationChange' => $monthlyStockRestockedValue - $monthlyStockIssuedValue,
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
