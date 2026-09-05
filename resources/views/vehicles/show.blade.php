@extends('layouts.master')
@section('page_title', 'Job Card: ' . $vehicle->plate)

@section('content')
<div class="content">

    {{-- 1. Financial & Build Summary Stat Bars --}}
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Customer Invoice</div>
                <div class="h4 font-weight-bold text-dark mb-0">{{ Qs::format_money($vehicle->invoice_total) }}</div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Labor Cost</div>
                <div class="h4 font-weight-bold text-dark mb-0">{{ Qs::format_money($vehicle->labor_cost) }}</div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Issued Parts Cost</div>
                <div class="h4 font-weight-bold text-dark mb-0">{{ Qs::format_money($vehicle->totalPartsCost()) }}</div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-2">
            @php $margin = $vehicle->grossMargin(); @endphp
            <div class="bg-light border rounded p-3 text-center {{ $margin >= 0 ? 'border-success' : 'border-danger' }}">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Job Gross Margin</div>
                <div class="h4 font-weight-bold {{ $margin >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                    {{ Qs::format_money($margin) }}
                </div>
            </div>
        </div>
    </div>

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
                                <form action="{{ route('vehicles.update_stage', $vehicle->id) }}" method="POST" class="border-top pt-3">
                                    @csrf @method('PUT')
                                    <h6 class="font-weight-semibold mb-2 text-dark">
                                        <i class="icon-git-branch mr-1 text-primary"></i> General Supervisor: Transition Stage &amp; Assign Lead
                                    </h6>
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
                                    <button type="submit" class="btn btn-primary font-weight-semibold btn-sm">
                                        <i class="icon-checkmark mr-1"></i> Update Stage &amp; Assignee
                                    </button>
                                    <span class="font-size-xs text-muted mt-1 d-block">
                                        Transitioning stage records an audit timestamp in the Stage History timeline.
                                    </span>
                                </form>
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
                        <table class="table table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Material Description</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-right">Unit Cost (KES)</th>
                                    <th class="text-right">Total Cost (KES)</th>
                                    <th class="text-center">Issued At</th>
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
                                    <td class="text-center font-weight-bold">{{ number_format($part->qty, 2) }}</td>
                                    <td class="text-right">{{ number_format($part->unit_cost, 2) }}</td>
                                    <td class="text-right font-weight-bold text-dark">{{ number_format($part->cost, 2) }}</td>
                                    <td class="text-center text-muted font-size-xs">{{ $part->issued_at->format('d M Y, H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted p-4">
                                        No materials or spare parts have been issued to this job card yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($vehicle->parts->count() > 0)
                            <tfoot>
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="4" class="text-right text-uppercase">Cumulative Parts Cost:</td>
                                    <td class="text-right text-primary h6 mb-0">{{ Qs::format_money($vehicle->totalPartsCost()) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- Tab 3: Stage History Timeline --}}
                <div class="tab-pane fade" id="tab-history">
                    <h6 class="font-weight-bold mb-3">Vehicle Stage Transition Audit Trail</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Stage Transitioned Into</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicle->stageHistories as $hist)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="badge badge-primary font-weight-semibold">{{ $hist->stage }}</span>
                                    </td>
                                    <td class="text-muted font-monospace">{{ $hist->transitioned_at->format('d M Y, H:i:s') }}</td>
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
                                    {{ $mat->name }} (Available: {{ $mat->qty }} {{ $mat->unit }} | KES {{ number_format($mat->unit_cost, 2) }}/{{ $mat->unit }})
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
