@extends('layouts.master')
@section('page_title', 'Vehicle Build Pipeline & Job Cards')

@section('content')
<div class="content">

    {{-- Main Container Card --}}
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-truck mr-2 text-primary"></i> Vehicle Build &amp; Stage Board Register
            </h6>
            <div class="header-elements">
                @if(Auth::user()->canEdit('vehicles'))
                <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm font-weight-semibold mr-1">
                    <i class="icon-plus2 mr-1"></i> New Job Card
                </a>
                @endif
                <button onclick="window.print()" class="btn btn-light btn-sm mr-2">
                    <i class="icon-printer mr-1"></i> Print Register
                </button>
                {!! Qs::getPanelOptions() !!}
            </div>
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

                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search plate, model, client..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-light btn-sm">Filter</button>
                    @if(request('stage') || request('search'))
                        <a href="{{ route('vehicles.index') }}" class="btn btn-link btn-sm text-danger">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Standard DataTables (Section 3) --}}
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns table-striped table-hover">
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 50px;">#</th>
                            <th>Plate / VIN</th>
                            <th>Make &amp; Model</th>
                            <th>Client / Account</th>
                            <th>Current Stage</th>
                            <th>Supervisor</th>
                            <th class="text-center">Days in Stage</th>
                            <th class="text-right">Invoice (KES)</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('vehicles.show', $row->id) }}" class="font-weight-bold text-primary">
                                    <span class="badge badge-secondary">{{ $row->plate }}</span>
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
                            <td>
                                <div>{{ $row->customer_name ?: '—' }}</div>
                                @if($row->customer_phone)
                                    <span class="font-size-xs text-muted">{{ $row->customer_phone }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badge = match(true) {
                                        str_contains($row->stage, '8.') => 'badge-success',
                                        $row->isStuck() => 'badge-danger',
                                        default => 'badge-primary'
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $row->stage }}</span>
                            </td>
                            <td>{{ $row->assigned_to ?: 'Unassigned' }}</td>
                            <td class="text-center">
                                @if($row->stage === '8. Completed & Dispatched')
                                    <span class="badge badge-success">Completed</span>
                                @elseif($row->isStuck())
                                    <span class="badge badge-danger font-weight-bold" title="Vehicle stuck &ge; 10 days!">
                                        <i class="icon-warning mr-1"></i> {{ $row->days_in_current_stage }}d (Stuck)
                                    </span>
                                @else
                                    <span class="badge badge-light border">{{ $row->days_in_current_stage }} days</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold text-dark">
                                {{ number_format($row->invoice_total, 2) }}
                            </td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('vehicles.show', $row->id) }}" class="dropdown-item">
                                                <i class="icon-eye"></i> View Job Card
                                            </a>
                                            <a href="{{ route('vehicles.print', $row->id) }}" target="_blank" class="dropdown-item">
                                                <i class="icon-printer"></i> Print Job Sheet
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
                                                <a href="{{ route('vehicles.edit', $row->id) }}" class="dropdown-item">
                                                    <i class="icon-pencil"></i> Edit Details
                                                </a>
                                             @endif
                                            @if(Auth::user()->canDelete())
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('vehicles.destroy', $row->id) }}" id="del-veh-{{ $row->id }}">
                                                @csrf @method('DELETE')
                                            </form>
                                            <a href="#" onclick="if(confirm('Delete Job Card #{{ $row->plate }}? This will permanently delete stage history and parts logs.')) { document.getElementById('del-veh-{{ $row->id }}').submit(); }" class="dropdown-item text-danger">
                                                <i class="icon-trash text-danger"></i> Delete Job Card
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
