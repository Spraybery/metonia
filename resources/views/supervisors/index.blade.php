@extends('layouts.master')
@section('page_title', 'Lead Supervisors Roster')

@section('content')
<div class="content">

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold d-flex align-items-center">
                <img src="{{ Qs::getSystemLogo() }}" alt="Metonia Logo" class="mr-2 border rounded p-1 bg-white shadow-xs" style="height: 34px; max-width: 140px; object-fit: contain;">
                <span><i class="icon-users4 mr-2 text-primary"></i> Workshop Lead Supervisors &amp; Workload Balancing</span>
            </h6>
            <div class="header-elements">
                @if(Auth::user()->canEdit('supervisors'))
                <button type="button" class="btn btn-primary btn-sm font-weight-semibold mr-1" data-toggle="modal" data-target="#modal-add-supervisor">
                    <i class="icon-plus2 mr-1"></i> Add Supervisor
                </button>
                @endif
                <button onclick="window.print()" class="btn btn-light btn-sm mr-2">
                    <i class="icon-printer mr-1"></i> Print Roster
                </button>
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
                            <th>Designation / Specialization</th>
                            <th>Assigned Build Stage</th>
                            <th>Work Shift</th>
                            <th>Contact Phone</th>
                            <th class="text-center">Live Active Workload</th>
                            <th class="text-center" style="width: 80px;">Action</th>
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
                            <td><span class="badge badge-light border">{{ $sup->title }}</span></td>
                            <td>
                                @if($sup->stage === 'All Stages')
                                    <span class="badge badge-secondary">All Stages (Lead)</span>
                                @else
                                    <span class="badge badge-primary">{{ $sup->stage }}</span>
                                @endif
                            </td>
                            <td>{{ $sup->shift ?: 'Standard Shift' }}</td>
                            <td>{{ $sup->phone ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $workload > 3 ? 'badge-warning' : 'badge-success' }} font-weight-bold px-2 py-1" style="font-size: 13px;">
                                    {{ $workload }} Active Vehicles
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(Auth::user()->canEdit('supervisors'))
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-sup-{{ $sup->id }}">
                                                <i class="icon-pencil"></i> Edit Details
                                            </a>
                                            @endif
                                            @if(Auth::user()->canDelete())
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('supervisors.destroy', $sup->id) }}" id="del-sup-{{ $sup->id }}">
                                                @csrf @method('DELETE')
                                            </form>
                                            <a href="#" onclick="if(confirm('Remove {{ $sup->name }} from active supervisor roster?')) { document.getElementById('del-sup-{{ $sup->id }}').submit(); }" class="dropdown-item text-danger">
                                                <i class="icon-trash text-danger"></i> Remove Supervisor
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

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
                                                        <label class="font-weight-semibold">Designation / Specialization <span class="text-danger">*</span></label>
                                                        <input type="text" name="title" class="form-control" value="{{ $sup->title }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Assigned Stage <span class="text-danger">*</span></label>
                                                        <select name="stage" class="form-control" required>
                                                            @foreach($stages as $st)
                                                                <option value="{{ $st }}" {{ $sup->stage === $st ? 'selected' : '' }}>{{ $st }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Shift Schedule</label>
                                                            <input type="text" name="shift" class="form-control" value="{{ $sup->shift }}">
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Contact Phone</label>
                                                            <input type="text" name="phone" class="form-control" value="{{ $sup->phone }}">
                                                        </div>
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
                        <label class="font-weight-semibold">Designation / Lead Role <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Powertrain & Engine Assembly Lead" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Assigned Plant Stage <span class="text-danger">*</span></label>
                        <select name="stage" class="form-control" required>
                            @foreach($stages as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Shift</label>
                            <input type="text" name="shift" class="form-control" placeholder="Day Shift (07:00 - 16:00)">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+254 700 123 456">
                        </div>
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
