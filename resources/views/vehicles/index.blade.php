@extends('layouts.master')
@section('page_title', 'Vehicle Build Pipeline & Job Cards')

@section('content')
<div class="content">

    {{-- Header Action & Page Overview --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 12px;">
        <div>
            <h5 class="font-weight-bold mb-1 text-dark">
                <i class="icon-truck text-primary mr-2"></i> Vehicle Build Pipeline &amp; Job Cards
            </h5>
            <p class="text-muted mb-0 font-size-sm">
                Track vehicle chassis intake, build stages, assigned lead supervisors, and job card status.
            </p>
        </div>
        <div>
            <a href="{{ route('vehicles.print_register', request()->query()) }}" target="_blank" class="btn btn-light font-weight-semibold shadow-xs mr-1">
                <i class="icon-printer mr-1"></i> Print Register
            </a>
            @if(Auth::user()->canEdit('vehicles'))
            <a href="{{ route('vehicles.create') }}" class="btn btn-primary font-weight-semibold shadow-xs">
                <i class="icon-plus2 mr-1"></i> New Job Card
            </a>
            @endif
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card border">
        <div class="card-header bg-light">
            <h6 class="card-title font-weight-bold mb-0">
                <i class="icon-truck mr-2 text-primary"></i> Vehicle Build &amp; Job Cards Register
            </h6>
        </div>

        <div class="card-body">
            {{-- Stage Filter Bar --}}
            <div class="mb-3 p-2 bg-light border rounded">
                <form action="{{ route('vehicles.index') }}" method="GET" class="form-inline d-flex flex-wrap" style="gap: 8px;">
                    <label class="font-weight-semibold mr-2">Filter Stage:</label>
                    <select name="stage" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">-- All Build Stages --</option>
                        @foreach($stages as $st)
                            <option value="{{ $st }}" {{ request('stage') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search chassis, vehicle, supervisor..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-light btn-sm font-weight-semibold">Filter Job Cards</button>
                    @if(request('stage') || request('search'))
                        <a href="{{ route('vehicles.index') }}" class="btn btn-link btn-sm text-danger">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Job Cards DataTables --}}
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns table-striped table-hover border">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Chassis Number</th>
                            <th>Vehicle Name</th>
                            <th>Engine No.</th>
                            <th>Bus Category</th>
                            <th>Job Number</th>
                            <th>Date In</th>
                            <th>Date Out</th>
                            <th>Delivery Date</th>
                            <th>Current Stage of Vehicle</th>
                            <th>Supervisor</th>
                            <th>Prepared By</th>
                            <th>Approved By</th>
                            <th>Date of Approval</th>
                            @if(Auth::user()->canEdit('vehicles') || Auth::user()->canDelete())
                            <th class="text-center no-export" style="width: 170px;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('vehicles.show', $row->id) }}" class="font-weight-bold text-primary">
                                    <span class="badge badge-secondary font-weight-bold">{{ $row->plate }}</span>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('vehicles.show', $row->id) }}" class="font-weight-semibold text-dark">
                                    {{ $row->make }} {{ $row->model }}
                                </a>
                                @if($row->year)
                                    <span class="text-muted font-size-xs">({{ $row->year }})</span>
                                @endif
                            </td>
                            <td class="font-size-sm">{{ $row->engine_no ?: '—' }}</td>
                            <td class="font-size-sm">{{ $row->bus_category ?: '—' }}</td>
                            <td>
                                <span class="badge badge-light border font-weight-semibold text-primary">#JC-{{ str_pad($row->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="font-size-sm text-muted">
                                {{ $row->intake_date ? $row->intake_date->format('d M Y') : $row->created_at->format('d M Y') }}
                            </td>
                            <td class="font-size-sm text-muted" data-order="{{ $row->date_out?->timestamp ?? 0 }}">
                                {{ $row->date_out ? $row->date_out->format('d M Y') : '—' }}
                            </td>
                            <td class="font-size-sm text-muted" data-order="{{ $row->delivery_date?->timestamp ?? 0 }}">
                                {{ $row->delivery_date ? $row->delivery_date->format('d M Y') : '—' }}
                            </td>
                            <td>
                                @php
                                    $badge = match(true) {
                                        str_contains($row->stage, '8.') => 'badge-success',
                                        $row->isStuck() => 'badge-danger',
                                        default => 'badge-primary'
                                    };
                                @endphp
                                <span class="badge {{ $badge }} px-2 py-1">{{ $row->stage }}</span>
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $row->assigned_to ?: 'Unassigned' }}</span>
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $row->prepared_by ?: '—' }}</span>
                            </td>
                            <td>
                                @if($row->isApproved())
                                    <span class="font-weight-semibold text-success"><i class="icon-checkmark-circle mr-1"></i>{{ $row->approved_by }}</span>
                                @else
                                    <span class="badge badge-light border text-warning font-weight-semibold">Pending approval</span>
                                @endif
                                @if(Auth::user()->canApproveJobCards())
                                <div class="mt-1">
                                    @if($row->isApproved())
                                        <form method="POST" action="{{ route('vehicles.revoke_approval', $row->id) }}" class="d-inline" onsubmit="return confirm('Revoke approval of Job Card #{{ $row->plate }}?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-link text-danger p-0 font-weight-semibold">Revoke</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('vehicles.approve', $row->id) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-xs btn-success font-weight-semibold py-0 px-2"><i class="icon-checkmark mr-1"></i> Approve</button>
                                        </form>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td class="font-size-sm text-muted" data-order="{{ $row->approved_at?->timestamp ?? 0 }}">
                                {{ $row->approved_at ? $row->approved_at->format('d M Y') : '—' }}
                            </td>
                            @if(Auth::user()->canEdit('vehicles') || Auth::user()->canDelete())
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                    @if(Auth::user()->canEdit('vehicles'))
                                        <a href="{{ route('vehicles.edit', $row->id) }}" class="btn btn-xs btn-outline-info font-weight-semibold px-2" title="Edit Vehicle Details">
                                            <i class="icon-pencil mr-1"></i> Edit
                                        </a>
                                    @endif

                                    @if(Auth::user()->canDelete())
                                        <form method="POST" action="{{ route('vehicles.destroy', $row->id) }}" id="del-veh-{{ $row->id }}" class="d-inline">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button type="button" onclick="if(confirm('Delete Job Card #{{ $row->plate }}? This will permanently delete stage history and parts logs.')) { document.getElementById('del-veh-{{ $row->id }}').submit(); }" class="btn btn-xs btn-outline-danger font-weight-semibold px-2" title="Delete Job Card">
                                            <i class="icon-trash mr-1"></i> Delete
                                        </button>
                                    @endif

                                    <div class="dropdown d-inline">
                                        <a href="#" class="list-icons-item ml-1" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('vehicles.show', $row->id) }}" class="dropdown-item">
                                                <i class="icon-eye text-primary"></i> View Job Card
                                            </a>
                                            <a href="{{ route('vehicles.print', $row->id) }}" target="_blank" class="dropdown-item">
                                                <i class="icon-printer text-muted"></i> Print Job Sheet
                                            </a>
                                            @if(Auth::user()->canEdit('vehicles'))
                                                @php $nextSt = Qs::getNextStage($row->stage); @endphp
                                                @if($nextSt)
                                                <form method="POST" action="{{ route('vehicles.update_stage', $row->id) }}" id="adv-veh-{{ $row->id }}">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="stage" value="{{ $nextSt }}">
                                                </form>
                                                <a href="#" onclick="event.preventDefault(); document.getElementById('adv-veh-{{ $row->id }}').submit();" class="dropdown-item text-success font-weight-bold">
                                                    <i class="icon-arrow-right8 text-success"></i> Advance to: {{ $nextSt }}
                                                </a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
