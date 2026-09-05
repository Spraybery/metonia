@extends('layouts.master')
@section('page_title', 'Workshop Operations Dashboard')

@section('content')
<div class="content">

    {{-- 1. Executive KPI Metric Stat Bars (Section 5 Standard) --}}
    <div class="row mb-3">
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center h-100">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Active Vehicles on Floor</div>
                <div class="h3 font-weight-bold text-dark mb-0">{{ number_format($totalActiveVehicles) }}</div>
                <div class="text-muted font-size-xs mt-1">Stages 1 through 7</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center h-100">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Stuck Vehicles (&ge; 10 Days)</div>
                <div class="h3 font-weight-bold {{ count($stuckVehicles) > 0 ? 'text-danger' : 'text-success' }} mb-0">
                    {{ count($stuckVehicles) }}
                </div>
                <div class="text-muted font-size-xs mt-1">{{ count($stuckVehicles) > 0 ? 'Urgent escalation required' : 'Assembly moving on schedule' }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center h-100">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Low-Stock Alert Items</div>
                <div class="h3 font-weight-bold {{ count($lowStockMaterials) > 0 ? 'text-warning' : 'text-success' }} mb-0">
                    {{ count($lowStockMaterials) }}
                </div>
                <div class="text-muted font-size-xs mt-1">Below safety reorder threshold</div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center h-100">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Total Stock Valuation</div>
                <div class="h3 font-weight-bold text-success mb-0">{{ Qs::format_money($totalStockValue) }}</div>
                <div class="text-muted font-size-xs mt-1">Store on-hand balance</div>
            </div>
        </div>
    </div>

    {{-- 2. Financial Margin Engine (MTD Performance) --}}
    <div class="card mb-3">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-cash3 mr-2 text-primary"></i> Monthly Financial Performance (MTD Margin Control)
            </h6>
            <div class="header-elements">
                <span class="badge badge-light border">{{ now()->format('F Y') }}</span>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="border rounded p-2 text-center bg-light">
                        <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Invoiced Revenue (MTD)</div>
                        <div class="h4 font-weight-bold text-primary mb-0">{{ Qs::format_money($revenueMtd) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="border rounded p-2 text-center bg-light">
                        <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Labor Cost (MTD)</div>
                        <div class="h4 font-weight-bold text-dark mb-0">{{ Qs::format_money($laborCostMtd) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="border rounded p-2 text-center bg-light">
                        <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Parts Issued Cost (MTD)</div>
                        <div class="h4 font-weight-bold text-dark mb-0">{{ Qs::format_money($partsCostMtd) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="border rounded p-2 text-center {{ $marginMtd >= 0 ? 'bg-success-light' : 'bg-danger-light' }} border">
                        <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Net Gross Margin (MTD)</div>
                        <div class="h4 font-weight-bold {{ $marginMtd >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                            {{ Qs::format_money($marginMtd) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Plant Build Pipeline Stage Distribution --}}
    <div class="card mb-3">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-git-commit mr-2 text-primary"></i> Assembly Plant Pipeline Distribution (8 Stages)
            </h6>
            <div class="header-elements">
                <a href="{{ route('vehicles.index') }}" class="btn btn-primary btn-sm font-weight-semibold">
                    <i class="icon-list mr-1"></i> View All Job Cards
                </a>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($stages as $stage)
                @php
                    $count = $pipelineCounts[$stage] ?? 0;
                    $pct = ($maxPipelineCount > 0) ? round(($count / $maxPipelineCount) * 100) : 0;
                    $badgeColor = match(true) {
                        str_contains($stage, '1.') => 'badge-secondary',
                        str_contains($stage, '8.') => 'badge-success',
                        default => 'badge-primary'
                    };
                @endphp
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="border rounded p-2 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="font-weight-semibold font-size-sm text-truncate" title="{{ $stage }}">{{ $stage }}</span>
                            <span class="badge {{ $badgeColor }}">{{ $count }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: {{ max($pct, 6) }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">
        {{-- 4. Stuck Vehicles Alert Engine Table --}}
        <div class="col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-header header-elements-inline bg-light">
                    <h6 class="card-title font-weight-bold text-danger">
                        <i class="icon-warning mr-2"></i> Pipeline Bottlenecks: Stuck Vehicles (&ge; 10 Days in Stage)
                    </h6>
                    <div class="header-elements">
                        <span class="badge badge-danger">{{ count($stuckVehicles) }} Vehicles Flagged</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($stuckVehicles) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light font-size-xs text-uppercase">
                                <tr>
                                    <th>Plate</th>
                                    <th>Model</th>
                                    <th>Current Stage</th>
                                    <th>Lead Supervisor</th>
                                    <th class="text-center">Days Stuck</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stuckVehicles as $vehicle)
                                <tr>
                                    <td>
                                        <a href="{{ route('vehicles.show', $vehicle->id) }}" class="font-weight-bold text-dark">
                                            {{ $vehicle->plate }}
                                        </a>
                                    </td>
                                    <td>{{ $vehicle->make }} {{ $vehicle->model }}</td>
                                    <td><span class="badge badge-warning">{{ $vehicle->stage }}</span></td>
                                    <td>{{ $vehicle->assigned_to ?: 'Unassigned' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-danger font-weight-bold px-2 py-1">
                                            {{ $vehicle->days_in_current_stage }} Days
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-light btn-xs">
                                            <i class="icon-arrow-right5"></i> Resolve
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-4 text-center text-muted">
                        <i class="icon-checkmark-circle text-success" style="font-size: 32px;"></i>
                        <div class="font-weight-semibold mt-2">No Bottlenecks Detected</div>
                        <div class="font-size-sm">All active vehicles are progressing within acceptable timeframes (< 10 days per stage).</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 5. Low-Stock Store Alerts & Tool Status --}}
        <div class="col-lg-5 mb-3">
            <div class="card mb-3">
                <div class="card-header header-elements-inline bg-light">
                    <h6 class="card-title font-weight-bold text-warning-800">
                        <i class="icon-alert mr-2 text-warning"></i> Store Low-Stock Alert Evaluator
                    </h6>
                    <div class="header-elements">
                        <a href="{{ route('materials.index') }}" class="btn btn-light btn-xs">View Store</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($lowStockMaterials) > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light font-size-xs text-uppercase">
                                <tr>
                                    <th>Material Item</th>
                                    <th class="text-center">On Hand</th>
                                    <th class="text-center">Reorder Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockMaterials->take(5) as $mat)
                                <tr>
                                    <td>
                                        <span class="font-weight-semibold">{{ $mat->name }}</span>
                                        <div class="font-size-xs text-muted">{{ $mat->category }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-danger">{{ number_format($mat->qty, 2) }} {{ $mat->unit }}</span>
                                    </td>
                                    <td class="text-center text-muted font-size-xs">
                                        {{ number_format($mat->low_stock, 2) }} {{ $mat->unit }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-3 text-center text-muted font-size-sm">
                        <i class="icon-check text-success"></i> All materials are above reorder safety limits.
                    </div>
                    @endif
                </div>
            </div>

            {{-- 6. Calibration Status --}}
            <div class="card">
                <div class="card-header header-elements-inline bg-light">
                    <h6 class="card-title font-weight-bold">
                        <i class="icon-wrench mr-2 text-primary"></i> Equipment Reliability &amp; Tools
                    </h6>
                    <div class="header-elements">
                        <a href="{{ route('tools.index') }}" class="btn btn-light btn-xs">Asset Register</a>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="font-size-sm">Available Equipment</span>
                        <span class="badge badge-success">{{ $toolsSummary['available'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="font-size-sm">Checked Out to Technicians</span>
                        <span class="badge badge-warning">{{ $toolsSummary['checked_out'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                        <span class="font-size-sm">Under Maintenance / Repair</span>
                        <span class="badge badge-secondary">{{ $toolsSummary['in_maintenance'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span class="font-size-sm font-weight-bold text-danger">Calibration Overdue</span>
                        <span class="badge badge-danger font-weight-bold">{{ $toolsSummary['calibration_overdue'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 7. Recent Activity Audit Trail --}}
    <div class="card mb-0">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-history mr-2 text-primary"></i> Live Workshop Operations Activity Log (Audit Trail)
            </h6>
            <div class="header-elements">
                <span class="badge badge-light border">Latest 10 Events</span>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="bg-light font-size-xs text-uppercase">
                        <tr>
                            <th style="width: 180px;">Timestamp</th>
                            <th style="width: 180px;">Actor / Staff</th>
                            <th>Event Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActivities as $act)
                        <tr>
                            <td class="font-size-xs text-muted font-monospace">{{ $act->created_at->format('d M Y, H:i:s') }}</td>
                            <td><span class="badge badge-light border font-weight-semibold">{{ $act->actor }}</span></td>
                            <td class="font-size-sm">{{ $act->message }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted p-3">No activity logged yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
