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

    private function getDashboardData(): array
    {
        $now = Carbon::now();
        $stages = Qs::getStages();

        // 1. Active Vehicles
        $activeVehicles = Vehicle::with(['stageHistories', 'parts'])
            ->where('stage', '!=', '8. Completed & Dispatched')
            ->get();

        $totalActiveVehicles = $activeVehicles->count();

        // 2. Stuck Vehicles Detector (>= 10 days in current stage)
        $stuckVehicles = $activeVehicles->filter(function (Vehicle $v) {
            return $v->isStuck();
        })->sortByDesc('days_in_current_stage')->values();

        // 3. Low-Stock Materials Alert Evaluator
        $lowStockMaterials = Material::all()->filter(function (Material $m) {
            return $m->isLowStock();
        })->values();

        // Total inventory valuation
        $totalStockValue = (float) Material::all()->sum(function (Material $m) {
            return $m->totalValue();
        });

        // 4. Stock Valuation Engine (MTD Movement Analytics)
        $mtdMovements = MaterialMovement::with('material')
            ->whereYear('date', $now->year)
            ->whereMonth('date', $now->month)
            ->get();

        $monthlyStockIssuedValue = (float) $mtdMovements
            ->where('type', 'out')
            ->sum(function (MaterialMovement $m) {
                return (float) $m->qty * (float) ($m->material->unit_cost ?? 0);
            });

        $monthlyStockRestockedValue = (float) $mtdMovements
            ->where('type', 'in')
            ->sum(function (MaterialMovement $m) {
                return (float) $m->qty * (float) ($m->material->unit_cost ?? 0);
            });

        $monthlyNetStockValuationChange = $monthlyStockRestockedValue - $monthlyStockIssuedValue;

        // 5. Plant Pipeline Distribution
        $pipelineCounts = [];
        foreach ($stages as $stage) {
            $pipelineCounts[$stage] = Vehicle::where('stage', $stage)->count();
        }
        $maxPipelineCount = max(array_values($pipelineCounts) ?: [1]);
        if ($maxPipelineCount <= 0) {
            $maxPipelineCount = 1;
        }

        // 6. Tool Calibration Overdue & Status
        $toolsSummary = [
            'total' => Tool::count(),
            'available' => Tool::where('status', 'Available')->count(),
            'checked_out' => Tool::where('status', 'Checked Out')->count(),
            'in_maintenance' => Tool::where('status', 'In Maintenance')->count(),
            'calibration_overdue' => Tool::whereNotNull('next_calibration')->where('next_calibration', '<', $now->toDateString())->count(),
        ];

        // 7. Recent Activity Feed
        $recentActivities = ActivityLog::orderByDesc('id')->take(10)->get();

        return [
            'totalActiveVehicles' => $totalActiveVehicles,
            'stuckVehicles' => $stuckVehicles,
            'lowStockMaterials' => $lowStockMaterials,
            'totalStockValue' => $totalStockValue,
            'monthlyStockIssuedValue' => $monthlyStockIssuedValue,
            'monthlyStockRestockedValue' => $monthlyStockRestockedValue,
            'monthlyNetStockValuationChange' => $monthlyNetStockValuationChange,
            'stages' => $stages,
            'pipelineCounts' => $pipelineCounts,
            'maxPipelineCount' => $maxPipelineCount,
            'toolsSummary' => $toolsSummary,
            'recentActivities' => $recentActivities,
            'totalSupervisors' => Supervisor::count(),
        ];
    }
}
