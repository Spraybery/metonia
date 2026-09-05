@extends('layouts.master')
@section('page_title', 'Stock Movement Log: ' . $material->name)

@section('content')
<div class="content">

    {{-- Main Container Card --}}
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold d-flex align-items-center">
                <img src="{{ Qs::getSystemLogo() }}" alt="Metonia Logo" class="mr-2 border rounded p-1 bg-white shadow-xs" style="height: 34px; max-width: 140px; object-fit: contain;">
                <span><i class="icon-history mr-2 text-primary"></i> Movement Audit Log: {{ $material->name }}</span>
            </h6>
            <div class="header-elements">
                <a href="{{ route('materials.index') }}" class="btn btn-light btn-sm mr-2">
                    <i class="icon-arrow-left7 mr-1"></i> Back to Inventory
                </a>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            {{-- Item Summary Bar --}}
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="bg-light border rounded p-2 text-center">
                        <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Category</div>
                        <div class="h5 font-weight-bold mb-0">{{ $material->category }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="bg-light border rounded p-2 text-center">
                        <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Current On-Hand</div>
                        <div class="h5 font-weight-bold {{ $material->isLowStock() ? 'text-danger' : 'text-success' }} mb-0">
                            {{ number_format($material->qty, 2) }} {{ $material->unit }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="bg-light border rounded p-2 text-center">
                        <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Unit Cost</div>
                        <div class="h5 font-weight-bold text-dark mb-0">{{ Qs::format_money($material->unit_cost) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="bg-light border rounded p-2 text-center">
                        <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Total Value</div>
                        <div class="h5 font-weight-bold text-success mb-0">{{ Qs::format_money($material->totalValue()) }}</div>
                    </div>
                </div>
            </div>

            {{-- Movement Table --}}
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns table-striped table-hover">
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 50px;">#</th>
                            <th>Date</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Quantity</th>
                            <th>Issued By</th>
                            <th>Issued To / Staff</th>
                            <th>Linked Vehicle Job Card</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($material->movements as $m)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="font-size-sm">{{ $m->date ? $m->date->format('d M Y') : '—' }}</td>
                            <td class="text-center">
                                @if($m->type === 'in')
                                    <span class="badge badge-success font-weight-bold">
                                        <i class="icon-arrow-down5 mr-1"></i> Stock In
                                    </span>
                                @else
                                    <span class="badge badge-danger font-weight-bold">
                                        <i class="icon-arrow-up5 mr-1"></i> Stock Out
                                    </span>
                                @endif
                            </td>
                            <td class="text-center font-weight-bold {{ $m->type === 'in' ? 'text-success' : 'text-danger' }}">
                                {{ $m->type === 'in' ? '+' : '-' }}{{ number_format($m->qty, 2) }} {{ $m->unit }}
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->issued_by ?: 'David Omondi' }}</span>
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->issued_to ?: ($m->person ?: 'Eng. Peter Kimani') }}</span>
                            </td>
                            <td>
                                @if($m->vehicle_id)
                                    <a href="{{ route('vehicles.show', $m->vehicle_id) }}" class="font-weight-semibold text-primary">
                                        {{ $m->vehicle_label ?: 'Job Card #' . $m->vehicle_id }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="font-size-sm text-muted">{{ $m->note ?: '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted p-4">No movement history recorded yet for this material.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
