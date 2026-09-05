@extends('layouts.master')
@section('page_title', 'Tools & Equipment Asset Register')

@section('content')
<div class="content">

    {{-- Summary KPI Stat Bars (Section 5 Standard) --}}
    <div class="row mb-3">
        <div class="col mb-2">
            <div class="bg-light border rounded p-3 text-center h-100">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Total Assets</div>
                <div class="h4 font-weight-bold text-dark mb-0">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col mb-2">
            <div class="bg-light border rounded p-3 text-center h-100">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Available</div>
                <div class="h4 font-weight-bold text-success mb-0">{{ $stats['available'] }}</div>
            </div>
        </div>
        <div class="col mb-2">
            <div class="bg-light border rounded p-3 text-center h-100">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Checked Out</div>
                <div class="h4 font-weight-bold text-warning mb-0">{{ $stats['checked_out'] }}</div>
            </div>
        </div>
        <div class="col mb-2">
            <div class="bg-light border rounded p-3 text-center h-100">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">In Maintenance</div>
                <div class="h4 font-weight-bold text-secondary mb-0">{{ $stats['in_maintenance'] }}</div>
            </div>
        </div>
        <div class="col mb-2">
            <div class="bg-light border rounded p-3 text-center h-100 {{ $stats['calibration_overdue'] > 0 ? 'border-danger' : '' }}">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Calibration Overdue</div>
                <div class="h4 font-weight-bold text-danger mb-0">{{ $stats['calibration_overdue'] }}</div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold d-flex align-items-center">
                <img src="{{ Qs::getSystemLogo() }}" alt="Metonia Logo" class="mr-2 border rounded p-1 bg-white shadow-xs" style="height: 34px; max-width: 140px; object-fit: contain;">
                <span><i class="icon-wrench mr-2 text-primary"></i> Workshop Tools &amp; Calibration Asset Register</span>
            </h6>
            <div class="header-elements">
                @if(Auth::user()->canEdit('tools'))
                <button type="button" class="btn btn-primary btn-sm font-weight-semibold mr-1" data-toggle="modal" data-target="#modal-add-tool">
                    <i class="icon-plus2 mr-1"></i> Register Asset
                </button>
                @endif
                <button onclick="window.print()" class="btn btn-light btn-sm mr-2">
                    <i class="icon-printer mr-1"></i> Print Register
                </button>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            {{-- Filter Bar --}}
            <div class="mb-3 p-2 bg-light border rounded">
                <form action="{{ route('tools.index') }}" method="GET" class="form-inline d-flex flex-wrap" style="gap: 8px;">
                    <label class="font-weight-semibold mr-2">Category:</label>
                    <select name="category" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">-- All Tool Categories --</option>
                        @foreach($categories as $c)
                            <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>

                    <label class="font-weight-semibold mr-2">Status:</label>
                    <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search tag, name, brand, location..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-light btn-sm">Filter</button>
                    @if(request('category') || request('status') || request('search'))
                        <a href="{{ route('tools.index') }}" class="btn btn-link btn-sm text-danger">Reset</a>
                    @endif
                </form>
            </div>

            {{-- DataTable --}}
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns table-striped table-hover">
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 50px;">#</th>
                            <th>Asset Tag</th>
                            <th>Equipment Description</th>
                            <th>Category</th>
                            <th>Brand / Location</th>
                            <th class="text-center">Status</th>
                            <th>Currently Held By</th>
                            <th class="text-center">Next Calibration</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tools as $tool)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="badge badge-secondary font-monospace">{{ $tool->asset_tag }}</span></td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $tool->name }}</span>
                            </td>
                            <td><span class="badge badge-light border">{{ $tool->category }}</span></td>
                            <td>
                                <div>{{ $tool->brand ?: '—' }}</div>
                                <span class="font-size-xs text-muted">{{ $tool->location ?: 'Main Crib' }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $statusBadge = match($tool->status) {
                                        'Available' => 'badge-success',
                                        'Checked Out' => 'badge-warning',
                                        'In Maintenance' => 'badge-secondary',
                                        default => 'badge-light'
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} font-weight-bold">{{ $tool->status }}</span>
                            </td>
                            <td>
                                @if($tool->assigned_to)
                                    <span class="font-weight-semibold text-dark">{{ $tool->assigned_to }}</span>
                                @else
                                    <span class="text-muted font-size-xs">In Tool Crib</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($tool->next_calibration)
                                    @if($tool->isCalibrationOverdue())
                                        <span class="badge badge-danger font-weight-bold" title="Calibration Overdue!">
                                            <i class="icon-warning mr-1"></i> {{ $tool->next_calibration->format('d M Y') }}
                                        </span>
                                    @elseif($tool->isCalibrationUpcoming())
                                        <span class="badge badge-warning font-weight-bold" title="Due in < 14 days">
                                            {{ $tool->next_calibration->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="badge badge-light border font-size-xs">
                                            {{ $tool->next_calibration->format('d M Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted font-size-xs">N/A</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(Auth::user()->canEdit('tools'))
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-tool-{{ $tool->id }}">
                                                <i class="icon-pencil"></i> Edit Asset
                                            </a>
                                            @endif
                                            @if(Auth::user()->canDelete())
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('tools.destroy', $tool->id) }}" id="del-tool-{{ $tool->id }}">
                                                @csrf @method('DELETE')
                                            </form>
                                            <a href="#" onclick="if(confirm('Decommission equipment [{{ $tool->asset_tag }}]?')) { document.getElementById('del-tool-{{ $tool->id }}').submit(); }" class="dropdown-item text-danger">
                                                <i class="icon-trash text-danger"></i> Decommission
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Modal --}}
                                @if(Auth::user()->canEdit('tools'))
                                <div id="modal-edit-tool-{{ $tool->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-slate-800 text-white">
                                                <h6 class="modal-title font-weight-bold">Edit Tool: [{{ $tool->asset_tag }}]</h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('tools.update', $tool->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Asset Tag <span class="text-danger">*</span></label>
                                                        <input type="text" name="asset_tag" class="form-control text-uppercase" value="{{ $tool->asset_tag }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Equipment Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $tool->name }}" required>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Category <span class="text-danger">*</span></label>
                                                            <select name="category" class="form-control" required>
                                                                @foreach($categories as $c)
                                                                    <option value="{{ $c }}" {{ $tool->category === $c ? 'selected' : '' }}>{{ $c }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Status <span class="text-danger">*</span></label>
                                                            <select name="status" class="form-control" required>
                                                                @foreach($statuses as $s)
                                                                    <option value="{{ $s }}" {{ $tool->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Brand / Manufacturer</label>
                                                            <input type="text" name="brand" class="form-control" value="{{ $tool->brand }}">
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Bay / Tool Crib Location</label>
                                                            <input type="text" name="location" class="form-control" value="{{ $tool->location }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Assigned Technician</label>
                                                            <input type="text" name="assigned_to" class="form-control" value="{{ $tool->assigned_to }}" placeholder="Technician Name">
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Next Calibration Date</label>
                                                            <input type="date" name="next_calibration" class="form-control" value="{{ $tool->next_calibration ? $tool->next_calibration->format('Y-m-d') : '' }}">
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

{{-- Register Tool Modal --}}
<div id="modal-add-tool" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate-800 text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-plus2 mr-2"></i> Register Equipment Asset in Register
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('tools.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-semibold">Asset Tag / Serial ID <span class="text-danger">*</span></label>
                        <input type="text" name="asset_tag" class="form-control text-uppercase" placeholder="e.g. TL-PNEU-108" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Tool Description / Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. 1/2-Inch Heavy Duty Impact Wrench" required>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-control" required>
                                @foreach($categories as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                @foreach($statuses as $s)
                                    <option value="{{ $s }}">{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Manufacturer / Brand</label>
                            <input type="text" name="brand" class="form-control" placeholder="e.g. Ingersoll Rand">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Tool Crib Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Bay 2 / Rack B">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Assigned Technician</label>
                            <input type="text" name="assigned_to" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Next Calibration Date</label>
                            <input type="date" name="next_calibration" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Register Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
