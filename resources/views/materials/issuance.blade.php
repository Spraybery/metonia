@extends('layouts.master')
@section('page_title', 'Outward Material Issuance Register')

@section('content')
<div class="content">

    {{-- Header Action & Page Overview --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 12px;">
        <div>
            <h5 class="font-weight-bold mb-1 text-dark">
                <i class="icon-arrow-up5 text-danger mr-2"></i> Outward Store Material Issuance Register
            </h5>
            <p class="text-muted mb-0 font-size-sm">
                Dedicated storekeeper module to issue raw materials to active vehicle job cards and track outward dispatches.
            </p>
        </div>
        <div>
            @if(Auth::user()->canEdit('materials'))
            <button type="button" class="btn btn-primary font-weight-semibold shadow-xs" data-toggle="modal" data-target="#modal-issue-vehicle">
                <i class="icon-arrow-up5 mr-1"></i> Issue Material Out of Store
            </button>
            @endif
            <a href="{{ route('materials.index') }}" class="btn btn-light ml-1 font-weight-semibold">
                <i class="icon-boxes mr-1"></i> Store Catalog
            </a>
        </div>
    </div>

    {{-- Summary Stats Bar --}}
    <div class="row mb-3">
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Total Outward Dispatches</div>
                <div class="h4 font-weight-bold text-danger mb-0">{{ number_format($outwardMovements->count()) }}</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Active Vehicles Serviced</div>
                <div class="h4 font-weight-bold text-primary mb-0">{{ number_format($activeVehicles->count()) }}</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Available Inventory SKUs</div>
                <div class="h4 font-weight-bold text-dark mb-0">{{ number_format($materials->count()) }}</div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card border">
        <div class="card-header header-elements-inline bg-light">
            <h6 class="card-title font-weight-bold">
                <i class="icon-list mr-2 text-danger"></i> Store Outward Issuance Register Log
            </h6>
            <div class="header-elements">
                <button onclick="window.print()" class="btn btn-light btn-sm mr-2">
                    <i class="icon-printer mr-1"></i> Print Register
                </button>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            {{-- Filter Bar --}}
            <div class="mb-3 p-2 bg-light border rounded">
                <form action="{{ route('materials.issuance') }}" method="GET" class="form-inline d-flex flex-wrap" style="gap: 8px;">
                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search material, vehicle, issuer or technician..." value="{{ request('search') }}" style="min-width: 280px;">
                    <button type="submit" class="btn btn-light btn-sm font-weight-semibold">Filter Register</button>
                    @if(request('search'))
                        <a href="{{ route('materials.issuance') }}" class="btn btn-link btn-sm text-danger">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns table-striped table-hover border">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Material Description</th>
                            <th class="text-center">Quantity</th>
                            <th>Vehicle Name / Destination</th>
                            <th>Issued By</th>
                            <th>Issued To</th>
                            <th>Date Issued</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outwardMovements as $m)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->material_name }}</span>
                            </td>
                            <td class="text-center font-weight-bold text-danger">
                                -{{ number_format($m->qty, 2) }} {{ $m->unit }}
                            </td>
                            <td>
                                @if($m->vehicle_id)
                                    <a href="{{ route('vehicles.show', $m->vehicle_id) }}" class="font-weight-semibold text-primary">
                                        {{ $m->vehicle_label ?: ('Job Card #' . $m->vehicle_id) }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $m->vehicle_label ?: 'General Store Deduction' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->issued_by ?: 'David Omondi' }}</span>
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->issued_to ?: ($m->person ?: 'Eng. Peter Kimani') }}</span>
                            </td>
                            <td class="font-size-sm text-muted">
                                {{ $m->date ? $m->date->format('d M Y') : $m->created_at->format('d M Y') }}
                            </td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(Auth::user()->canEdit('materials'))
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-issuance-{{ $m->id }}">
                                                <i class="icon-pencil text-primary"></i> Edit Issuance
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('materials.movement.destroy', $m->id) }}" id="del-issuance-{{ $m->id }}">
                                                @csrf @method('DELETE')
                                            </form>
                                            <a href="#" onclick="if(confirm('Delete issuance record of {{ $m->material_name }}? Stock will be reverted.')) { document.getElementById('del-issuance-{{ $m->id }}').submit(); }" class="dropdown-item text-danger">
                                                <i class="icon-trash text-danger"></i> Delete Issuance
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Issuance Modal --}}
                                @if(Auth::user()->canEdit('materials'))
                                <div id="modal-edit-issuance-{{ $m->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-primary text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="icon-pencil mr-1"></i> Edit Issuance Record: {{ $m->material_name }}
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('materials.movement.update', $m->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Material Description</label>
                                                        <input type="text" class="form-control" value="{{ $m->material_name }}" disabled>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Vehicle Name / Destination <span class="text-danger">*</span></label>
                                                        <select name="vehicle_id" class="form-control" required>
                                                            <option value="">-- Select Active Vehicle --</option>
                                                            @foreach($activeVehicles as $v)
                                                                <option value="{{ $v->id }}" {{ $m->vehicle_id == $v->id ? 'selected' : '' }}>
                                                                    {{ $v->plate }} — {{ $v->make }} {{ $v->model }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="col-6 form-group">
                                                            <label class="font-weight-semibold">Quantity Issued <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" min="0.01" name="qty" class="form-control" value="{{ $m->qty }}" required>
                                                        </div>
                                                        <div class="col-6 form-group">
                                                            <label class="font-weight-semibold">Date Issued <span class="text-danger">*</span></label>
                                                            <input type="date" name="date" class="form-control" value="{{ $m->date ? $m->date->format('Y-m-d') : date('Y-m-d') }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="col-6 form-group">
                                                            <label class="font-weight-semibold">Issued By (Storekeeper) <span class="text-danger">*</span></label>
                                                            <input type="text" name="issued_by" class="form-control" value="{{ $m->issued_by ?: Auth::user()->name }}" required>
                                                        </div>
                                                        <div class="col-6 form-group">
                                                            <label class="font-weight-semibold">Issued To (Technician) <span class="text-danger">*</span></label>
                                                            <input type="text" name="issued_to" class="form-control" value="{{ $m->issued_to ?: $m->person }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Correction Note / Reason</label>
                                                        <textarea name="note" class="form-control" rows="2" placeholder="Reason for correction...">{{ $m->note }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary font-weight-semibold">Save Corrections</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted p-4">
                                No outward material issuances logged in the store register yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Shopkeeper Modal: Issue Material Out of Store --}}
<div id="modal-issue-vehicle" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-arrow-up5 mr-2"></i> Issue Store Material Out of Inventory (Storekeeper)
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="issue-movement-form" method="POST">
                @csrf
                <input type="hidden" name="type" value="out">
                <div class="modal-body">
                    <div class="alert alert-info py-2 font-size-sm">
                        <i class="icon-info22 mr-1"></i> Record outward material issuance. Stock is immediately deducted and registered to the target vehicle's Job Card.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Material Description <span class="text-danger">*</span></label>
                        <select id="issue-mat-select" class="form-control select-search" required onchange="updateIssueAction(this.value)">
                            <option value="">-- Select Material Description --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" {{ $m->qty <= 0 ? 'disabled' : '' }}>
                                    {{ $m->name }} (Available: {{ $m->qty }} {{ $m->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Vehicle Name / Destination <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-control" required id="issue-vehicle-select" onchange="autoFillTechnician(this)">
                            <option value="">-- Choose Target Vehicle --</option>
                            @foreach($activeVehicles as $v)
                                <option value="{{ $v->id }}" data-lead="{{ $v->assigned_to }}">
                                    {{ $v->plate }} — {{ $v->make }} {{ $v->model }} (Stage: {{ $v->stage }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="qty" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Date Materials Issued <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Issued By (Storekeeper) <span class="text-danger">*</span></label>
                            <input type="text" name="issued_by" class="form-control" value="{{ Auth::user()->name }}" required>
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Issued To (Technician) <span class="text-danger">*</span></label>
                            <input type="text" name="issued_to" id="technician-person-input" class="form-control" placeholder="e.g. Eng. Peter Kimani" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Issuance Purpose / Workshop Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="e.g. Chassis cross-member structural fabrication"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Confirm Outward Store Issuance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateIssueAction(matId) {
        if (matId) {
            document.getElementById('issue-movement-form').action = '/materials/' + matId + '/movement';
        }
    }

    function autoFillTechnician(selectElem) {
        var opt = selectElem.options[selectElem.selectedIndex];
        if (opt && opt.dataset.lead) {
            document.getElementById('technician-person-input').value = opt.dataset.lead;
        }
    }
</script>
@endpush

@endsection
