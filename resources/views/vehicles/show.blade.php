@extends('layouts.master')
@section('page_title', 'Job Card: ' . $vehicle->plate)

@section('content')
<div class="content">



    {{-- Main Container Card --}}
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-file-text2 mr-2 text-primary"></i> Job Card #{{ $vehicle->plate }} — {{ $vehicle->make }} {{ $vehicle->model }}
            </h6>
            <div class="header-elements">
                <a href="{{ route('vehicles.print', $vehicle->id) }}" target="_blank" class="btn btn-light btn-sm mr-1">
                    <i class="icon-printer mr-1"></i> Print Job Card
                </a>
                @if(Auth::user()->canEdit('vehicles'))
                <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-primary btn-sm font-weight-semibold mr-1">
                    <i class="icon-pencil mr-1"></i> Edit
                </a>
                @endif
                <a href="{{ route('vehicles.index') }}" class="btn btn-light btn-sm mr-2">
                    <i class="icon-arrow-left7 mr-1"></i> Back
                </a>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">

            {{-- Tab Navigation Pattern (Section 4 Standard) --}}
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item">
                    <a href="#tab-stage" class="nav-link active" data-toggle="tab">
                        <i class="icon-git-commit mr-2"></i> Stage Progression &amp; Checklist
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tab-parts" class="nav-link" data-toggle="tab">
                        <i class="icon-wrench mr-2"></i> Issued Parts ({{ $vehicle->parts->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tab-history" class="nav-link" data-toggle="tab">
                        <i class="icon-history mr-2"></i> Transition History ({{ $vehicle->stageHistories->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tab-details" class="nav-link" data-toggle="tab">
                        <i class="icon-info22 mr-2"></i> Specifications &amp; Client
                    </a>
                </li>
            </ul>

            <div class="tab-content">

                {{-- Tab 1: Stage Progression & Checklist --}}
                <div class="tab-pane fade show active" id="tab-stage">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="border rounded p-3 bg-light mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold text-uppercase mb-0">Current Active Stage:</h6>
                                    <div>
                                        <span class="text-muted font-size-xs text-uppercase font-weight-semibold mr-1">Assigned Lead:</span>
                                        <span class="badge badge-light border text-dark font-weight-bold">
                                            <i class="icon-user-tie mr-1 text-primary"></i> {{ $vehicle->assigned_to ?: 'Unassigned' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="h4 font-weight-bold text-primary mb-0 mr-3">{{ $vehicle->stage }}</span>
                                    @if($vehicle->isStuck())
                                        <span class="badge badge-danger font-weight-bold p-2">
                                            <i class="icon-warning mr-1"></i> STUCK (&ge; 10 DAYS)
                                        </span>
                                    @else
                                        <span class="badge badge-secondary p-2">{{ $vehicle->days_in_current_stage }} Days in this stage</span>
                                    @endif
                                </div>

                                @if(Auth::user()->canEdit('vehicles'))
                                <div class="border-top pt-3">
                                    @if($nextStage)
                                    <form action="{{ route('vehicles.update_stage', $vehicle->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="stage" value="{{ $nextStage }}">
                                        <h6 class="font-weight-semibold mb-2 text-dark">
                                            <i class="icon-git-branch mr-1 text-primary"></i> Advance Build Pipeline
                                        </h6>
                                        <div class="form-row mb-2">
                                            <div class="col-md-7 mb-2">
                                                <label class="font-size-xs font-weight-semibold text-muted text-uppercase">Lead Supervisor / Technician:</label>
                                                <select name="assigned_to" class="form-control">
                                                    <option value="">-- Select Assigned Lead --</option>
                                                    @foreach($supervisors as $sup)
                                                        <option value="{{ $sup->name }}" {{ $vehicle->assigned_to === $sup->name ? 'selected' : '' }}>
                                                            {{ $sup->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success font-weight-bold">
                                            <i class="icon-arrow-right8 mr-1"></i> Advance to: {{ $nextStage }}
                                        </button>
                                        <span class="font-size-xs text-muted mt-1 d-block">
                                            Moves the vehicle to the next stage in sequence and records an audit timestamp in the Stage History timeline.
                                        </span>
                                    </form>
                                    @else
                                    <div class="alert alert-success mb-0 d-flex align-items-center">
                                        <i class="icon-checkmark-circle mr-2"></i> Build Complete &amp; Dispatched — this vehicle has finished the pipeline.
                                    </div>
                                    @endif

                                    <a href="#override-stage" data-toggle="collapse" class="d-inline-block font-size-xs text-muted mt-3">
                                        <i class="icon-tools mr-1"></i> Manual override / correction (jump to a specific stage)
                                    </a>
                                    <div class="collapse mt-2" id="override-stage">
                                        <form action="{{ route('vehicles.update_stage', $vehicle->id) }}" method="POST" class="border-top pt-2">
                                            @csrf @method('PUT')
                                            <div class="form-row mb-2">
                                                <div class="col-md-7 mb-2">
                                                    <label class="font-size-xs font-weight-semibold text-muted text-uppercase">Vehicle Build Stage (1-8):</label>
                                                    <select name="stage" class="form-control" required>
                                                        @foreach($stages as $st)
                                                            <option value="{{ $st }}" {{ $vehicle->stage === $st ? 'selected' : '' }}>
                                                                {{ $st }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5 mb-2">
                                                    <label class="font-size-xs font-weight-semibold text-muted text-uppercase">Lead Supervisor / Technician:</label>
                                                    <select name="assigned_to" class="form-control">
                                                        <option value="">-- Select Assigned Lead --</option>
                                                        @foreach($supervisors as $sup)
                                                            <option value="{{ $sup->name }}" {{ $vehicle->assigned_to === $sup->name ? 'selected' : '' }}>
                                                                {{ $sup->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-light border font-weight-semibold btn-sm">
                                                <i class="icon-checkmark mr-1"></i> Set Stage &amp; Assignee
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="border rounded p-3 bg-white mb-3">
                                <h6 class="font-weight-bold text-uppercase mb-2">Stage Quality Checklist</h6>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Milestones Completed:</span>
                                    <span class="font-weight-bold">{{ $vehicle->checklist_done }} / {{ $vehicle->checklist_total }}</span>
                                </div>
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: {{ $vehicle->checklistPercentage() }}%"></div>
                                </div>

                                @if(Auth::user()->canEdit('vehicles'))
                                <form action="{{ route('vehicles.update_checklist', $vehicle->id) }}" method="POST" class="border-top pt-2">
                                    @csrf @method('PUT')
                                    <div class="form-row">
                                        <div class="col-6">
                                            <label class="font-size-xs text-muted">Done Steps</label>
                                            <input type="number" name="checklist_done" class="form-control form-control-sm" value="{{ $vehicle->checklist_done }}" min="0" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="font-size-xs text-muted">Total Steps</label>
                                            <input type="number" name="checklist_total" class="form-control form-control-sm" value="{{ $vehicle->checklist_total }}" min="1" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-light btn-sm btn-block mt-2 font-weight-semibold">
                                        Save Checklist Progress
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 2: Issued Parts & Material Movements --}}
                <div class="tab-pane fade" id="tab-parts">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0">Parts Issued from Store Inventory</h6>
                        @if(Auth::user()->canEdit('vehicles'))
                        <button type="button" class="btn btn-primary btn-sm font-weight-semibold" data-toggle="modal" data-target="#modal-issue-part">
                            <i class="icon-plus2 mr-1"></i> Issue Part from Store
                        </button>
                        @endif
                    </div>


                    <div class="table-responsive">
                        <table class="table table-striped table-hover border">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Material Description</th>
                                    <th class="text-center">Quantity</th>
                                    <th>Issued By</th>
                                    <th>Issued To</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vehicle->parts as $part)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="font-weight-semibold">{{ $part->material_name }}</span>
                                        @if($part->material)
                                            <span class="badge badge-light border ml-1">{{ $part->material->category }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">{{ number_format($part->qty, 2) }} {{ $part->material?->unit }}</td>
                                    <td>
                                        <span class="font-weight-semibold text-dark">{{ $part->issued_by ?? 'David Omondi' }}</span>
                                        @if($part->issued_at)
                                            <span class="d-block text-muted font-size-xs">{{ $part->issued_at->format('d M Y, H:i') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-semibold text-dark">{{ $part->issued_to ?? ($vehicle->assigned_to ?: 'Eng. Peter Kimani') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted p-4">
                                        No materials or spare parts have been issued to this job card yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 3: Stage History Timeline --}}
                <div class="tab-pane fade" id="tab-history">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0">
                            <i class="icon-history text-primary mr-1"></i> Full 8-Stage Build Pipeline History
                        </h6>
                        <span class="badge badge-light border font-size-xs">
                            Current: <strong class="text-primary">{{ $vehicle->stage }}</strong>
                        </span>
                    </div>

                    {{-- Visual 8-Stage Pipeline Stepper --}}
                    <div class="card bg-light border mb-4">
                        <div class="card-body p-3">
                            <div class="row text-center">
                                @foreach($stageTimeline as $row)
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="p-2 rounded border bg-white shadow-xs {{ $row['is_current'] ? 'border-primary' : ($row['is_completed'] ? 'border-success' : '') }}">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle font-weight-bold mb-1 {{ $row['is_current'] ? 'bg-primary text-white' : ($row['is_completed'] ? 'bg-success text-white' : 'bg-light text-muted border') }}" style="width: 32px; height: 32px; font-size: 13px;">
                                            @if($row['is_completed'])
                                                <i class="icon-checkmark2"></i>
                                            @else
                                                {{ $row['stage_number'] }}
                                            @endif
                                        </div>
                                        <div class="font-weight-bold font-size-xs text-truncate {{ $row['is_current'] ? 'text-primary' : ($row['is_completed'] ? 'text-success' : 'text-muted') }}" title="{{ $row['stage'] }}">
                                            Stage {{ $row['stage_number'] }}
                                        </div>
                                        <div class="text-muted font-size-xs text-truncate" title="{{ $row['stage'] }}">
                                            {{ Str::after($row['stage'], '. ') }}
                                        </div>
                                        <div class="mt-1">
                                            @if($row['is_current'])
                                                <span class="badge badge-primary font-size-xs">Current</span>
                                            @elseif($row['is_completed'])
                                                <span class="badge badge-success font-size-xs">Done</span>
                                            @else
                                                <span class="badge badge-light border text-muted font-size-xs">Pending</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table class="table table-striped table-hover border">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 60px;">Stage #</th>
                                    <th>Build Stage Name</th>
                                    <th class="text-center">Status</th>
                                    <th>Entered Stage</th>
                                    <th>Exited Stage</th>
                                    <th class="text-center">Dwell Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stageTimeline as $row)
                                <tr class="{{ $row['is_current'] ? 'bg-light font-weight-bold' : '' }}">
                                    <td class="text-center">
                                        <span class="badge badge-pill {{ $row['is_current'] ? 'badge-primary' : ($row['is_completed'] ? 'badge-success' : 'badge-light border') }}">
                                            {{ $row['stage_number'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-semibold {{ $row['is_current'] ? 'text-primary' : 'text-dark' }}">
                                            {{ $row['stage'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($row['is_current'])
                                            <span class="badge badge-primary p-1">
                                                <i class="icon-spinner11 spinner mr-1"></i> Active Stage
                                            </span>
                                        @elseif($row['is_completed'])
                                            <span class="badge badge-success p-1">
                                                <i class="icon-checkmark mr-1"></i> Completed
                                            </span>
                                        @else
                                            <span class="badge badge-light border text-muted p-1">
                                                <i class="icon-hour-glass mr-1"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-muted font-size-xs">
                                        {{ $row['entered_at'] ? $row['entered_at']->format('d M Y, H:i:s') : '—' }}
                                    </td>
                                    <td class="text-muted font-size-xs">
                                        @if($row['is_current'])
                                            <span class="text-primary font-weight-bold">Currently in this stage <span class="badge badge-success ml-1">Still here</span></span>
                                        @elseif($row['left_at'])
                                            {{ $row['left_at']->format('d M Y, H:i:s') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($row['entered_at'])
                                            <span class="badge {{ $row['is_current'] ? ($vehicle->isStuck() ? 'badge-danger' : 'badge-primary') : 'badge-light border' }}">
                                                {{ $row['duration_days'] }} day{{ $row['duration_days'] === 1 ? '' : 's' }}
                                            </span>
                                        @else
                                            <span class="text-muted font-size-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 4: Specifications & Client Profile --}}
                <div class="tab-pane fade" id="tab-details">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light mb-3">
                                <h6 class="font-weight-bold text-uppercase border-bottom pb-2 mb-3">Vehicle Details</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 140px;">Plate / VIN:</td>
                                        <td class="font-weight-bold">{{ $vehicle->plate }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Manufacturer:</td>
                                        <td>{{ $vehicle->make }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Model:</td>
                                        <td>{{ $vehicle->model }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Manufacture Year:</td>
                                        <td>{{ $vehicle->year ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Plant Intake Date:</td>
                                        <td>{{ $vehicle->intake_date ? $vehicle->intake_date->format('d M Y, H:i') : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Lead Supervisor:</td>
                                        <td><span class="badge badge-light border">{{ $vehicle->assigned_to ?: 'Unassigned' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Completed Date:</td>
                                        <td>{{ $vehicle->completed_at ? $vehicle->completed_at->format('d M Y, H:i') : 'In Progress' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light mb-3">
                                <h6 class="font-weight-bold text-uppercase border-bottom pb-2 mb-3">Client &amp; Technical Notes</h6>
                                <table class="table table-sm table-borderless mb-3">
                                    <tr>
                                        <td class="text-muted" style="width: 140px;">Customer / Account:</td>
                                        <td class="font-weight-semibold">{{ $vehicle->customer_name ?: '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Customer Phone:</td>
                                        <td>{{ $vehicle->customer_phone ?: '—' }}</td>
                                    </tr>
                                </table>

                                <div class="font-weight-semibold text-muted font-size-xs text-uppercase mb-1">Intake / Technical Notes:</div>
                                <div class="p-2 bg-white border rounded font-size-sm text-dark">
                                    {{ $vehicle->notes ?: 'No specific diagnostic intake notes recorded.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- Issue Part Modal (Atomic Inventory Transaction) --}}
<div id="modal-issue-part" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate-800 text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-wrench mr-2"></i> Issue Part to Job Card #{{ $vehicle->plate }}
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('vehicles.issue_part', $vehicle->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 font-size-sm">
                        <i class="icon-info22 mr-1"></i> Parts issued are deducted automatically and atomically from store inventory.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Select Store Material / Part <span class="text-danger">*</span></label>
                        <select name="material_id" class="form-control select-search" required id="select-material">
                            <option value="">-- Choose Item --</option>
                            @foreach($materials as $mat)
                                <option value="{{ $mat->id }}" data-unit="{{ $mat->unit }}" data-qty="{{ $mat->qty }}" data-cost="{{ $mat->unit_cost }}">
                                    {{ $mat->name }} (Available: {{ $mat->qty }} {{ $mat->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Quantity to Issue <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="qty" class="form-control" required placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Technician / Person Taking Material <span class="text-danger">*</span></label>
                        <input type="text" name="person" class="form-control" value="{{ $vehicle->assigned_to ?: Auth::user()->name }}" required placeholder="e.g. Eng. Peter Kimani">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Confirm &amp; Deduct Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
