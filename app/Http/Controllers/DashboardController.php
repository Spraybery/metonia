<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\ActivityLog;
use App\Models\Material;
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
        $totalStockValue = Material::all()->sum(function (Material $m) {
            return $m->totalValue();
        });

        // 4. Financial Margin Engine (MTD)
        $completedThisMonth = Vehicle::with('parts')
            ->whereNotNull('completed_at')
            ->whereYear('completed_at', $now->year)
            ->whereMonth('completed_at', $now->month)
            ->get();

        $revenueMtd = (float) $completedThisMonth->sum('invoice_total');
        $laborCostMtd = (float) $completedThisMonth->sum('labor_cost');
        $partsCostMtd = (float) $completedThisMonth->sum(function (Vehicle $v) {
            return $v->totalPartsCost();
        });
        $totalCostMtd = $laborCostMtd + $partsCostMtd;
        $marginMtd = $revenueMtd - $totalCostMtd;

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
            'revenueMtd' => $revenueMtd,
            'laborCostMtd' => $laborCostMtd,
            'partsCostMtd' => $partsCostMtd,
            'totalCostMtd' => $totalCostMtd,
            'marginMtd' => $marginMtd,
            'stages' => $stages,
            'pipelineCounts' => $pipelineCounts,
            'maxPipelineCount' => $maxPipelineCount,
            'toolsSummary' => $toolsSummary,
            'recentActivities' => $recentActivities,
            'totalSupervisors' => Supervisor::count(),
        ];
    }
}
