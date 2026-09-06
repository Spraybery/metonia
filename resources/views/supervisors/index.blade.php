@extends('layouts.master')
@section('page_title', 'Lead Supervisors Roster')

@section('content')
<div class="content">

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-users4 mr-2 text-primary"></i> Workshop Lead Supervisors &amp; Workload Balancing
            </h6>
            <div class="header-elements">
                @if(Auth::user()->canEdit('supervisors'))
                <button type="button" class="btn btn-primary btn-sm font-weight-semibold mr-1" data-toggle="modal" data-target="#modal-add-supervisor">
                    <i class="icon-plus2 mr-1"></i> Add Supervisor
                </button>
                @endif
                <a href="{{ route('supervisors.print') }}" target="_blank" class="btn btn-light btn-sm mr-2">
                    <i class="icon-printer mr-1"></i> Print Roster
                </a>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns table-striped table-hover">
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 50px;">#</th>
                            <th>Supervisor Name</th>
                            <th>Assigned Build Stage</th>
                            <th>Contact Phone</th>
                            <th class="text-center">Live Active Workload</th>
                            @if(Auth::user()->canEdit('supervisors') || Auth::user()->canDelete())
                            <th class="text-center no-export" style="width: 170px;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supervisors as $sup)
                        @php $workload = $sup->activeSupervisedVehiclesCount(); @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-bold text-dark">{{ $sup->name }}</span>
                            </td>
                            <td>
                                @if($sup->stage === 'All Stages')
                                    <span class="badge badge-secondary">All Stages (Lead)</span>
                                @else
                                    <span class="badge badge-primary">{{ $sup->stage }}</span>
                                @endif
                            </td>
                            <td>{{ $sup->phone ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $workload > 3 ? 'badge-warning' : 'badge-success' }} font-weight-bold px-2 py-1" style="font-size: 13px;">
                                    {{ $workload }} Active Vehicles
                                </span>
                            </td>
                            @if(Auth::user()->canEdit('supervisors') || Auth::user()->canDelete())
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                    @if(Auth::user()->canEdit('supervisors'))
                                        <button type="button" class="btn btn-xs btn-outline-info font-weight-semibold px-2" data-toggle="modal" data-target="#modal-edit-sup-{{ $sup->id }}" title="Edit Details">
                                            <i class="icon-pencil mr-1"></i> Edit
                                        </button>
                                    @endif
                                    @if(Auth::user()->canDelete())
                                        <form method="POST" action="{{ route('supervisors.destroy', $sup->id) }}" id="del-sup-{{ $sup->id }}" class="d-inline">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button type="button" onclick="if(confirm('Remove {{ $sup->name }} from active supervisor roster?')) { document.getElementById('del-sup-{{ $sup->id }}').submit(); }" class="btn btn-xs btn-outline-danger font-weight-semibold px-2" title="Remove Supervisor">
                                            <i class="icon-trash mr-1"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                            @endif

                                {{-- Edit Modal --}}
                                @if(Auth::user()->canEdit('supervisors'))
                                <div id="modal-edit-sup-{{ $sup->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-slate-800 text-white">
                                                <h6 class="modal-title font-weight-bold">Edit Supervisor: {{ $sup->name }}</h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('supervisors.update', $sup->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Supervisor Full Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $sup->name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Assigned Stage <span class="text-danger">*</span></label>
                                                        <select name="stage" class="form-control" required>
                                                            @foreach($stages as $st)
                                                                <option value="{{ $st }}" {{ $sup->stage === $st ? 'selected' : '' }}>{{ $st }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Contact Phone</label>
                                                        <input type="text" name="phone" class="form-control" value="{{ $sup->phone }}" placeholder="e.g. +254 700 123 456">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary font-weight-semibold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Add Supervisor Modal --}}
<div id="modal-add-supervisor" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate-800 text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-plus2 mr-2"></i> Add Lead Supervisor to Roster
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('supervisors.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Eng. Peter Kimani" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Assigned Plant Stage <span class="text-danger">*</span></label>
                        <select name="stage" class="form-control" required>
                            @foreach($stages as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Contact Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+254 700 123 456">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Add to Roster
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
