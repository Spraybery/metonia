@extends('layouts.master')
@section('page_title', 'Inventory & Safety Gear Restock Requisition')

@section('content')
<div class="content">

    {{-- Filter Header Card --}}
    <div class="card mb-3">
        <div class="card-header header-elements-inline bg-light">
            <h6 class="card-title font-weight-bold">
                <i class="icon-clipboard3 mr-2 text-primary"></i> Inventory &amp; Safety Gear Restock Requisition List
            </h6>
            <div class="header-elements">
                <a href="{{ route('materials.restock_needed.print', request()->query()) }}" target="_blank" class="btn btn-emerald text-white font-weight-bold btn-sm shadow-xs">
                    <i class="icon-printer mr-1"></i> Print Restock Requisition
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-7 mb-2 mb-md-0">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <a href="{{ route('materials.restock_needed', array_merge(request()->except('type'), ['type' => 'all'])) }}" 
                           class="btn btn-light {{ $type === 'all' ? 'active font-weight-bold text-dark border-primary' : '' }}">
                            <i class="icon-boxes mr-1"></i> All Low-Stock Items
                            <span class="badge badge-primary badge-pill ml-1">{{ $allLowStock->count() }}</span>
                        </a>

                        <a href="{{ route('materials.restock_needed', array_merge(request()->except('type'), ['type' => 'materials'])) }}" 
                           class="btn btn-light {{ $type === 'materials' ? 'active font-weight-bold text-dark border-warning' : '' }}">
                            <i class="icon-wrench mr-1 text-warning"></i> Store Raw Materials Only
                            <span class="badge badge-warning text-dark badge-pill ml-1">{{ $lowStockMaterials->count() }}</span>
                        </a>

                        <a href="{{ route('materials.restock_needed', array_merge(request()->except('type'), ['type' => 'safety'])) }}" 
                           class="btn btn-light {{ $type === 'safety' ? 'active font-weight-bold text-dark border-danger' : '' }}">
                            <i class="icon-shield-notice mr-1 text-danger"></i> Safety Gears &amp; PPE Only
                            <span class="badge badge-danger badge-pill ml-1">{{ $lowStockSafety->count() }}</span>
                        </a>
                    </div>
                </div>

                <div class="col-md-5">
                    <form method="GET" action="{{ route('materials.restock_needed') }}" class="d-flex">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="text" name="search" class="form-control mr-2" placeholder="Search item, category, or supplier..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary font-weight-semibold px-3">Filter</button>
                        @if(request()->filled('search'))
                            <a href="{{ route('materials.restock_needed', ['type' => $type]) }}" class="btn btn-light ml-1">Reset</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Restock Needed Table Card --}}
    <div class="card">
        <div class="card-header header-elements-inline bg-light">
            <h6 class="card-title font-weight-bold mb-0">
                @if($type === 'materials')
                    <i class="icon-alert text-warning mr-2"></i> Store Raw Materials Needed
                @elseif($type === 'safety')
                    <i class="icon-shield-notice text-danger mr-2"></i> Worker Safety Gears &amp; PPE Needed
                @else
                    <i class="icon-list mr-2 text-primary"></i> Combined Restock Requisition List
                @endif
                <span class="badge badge-flat border-danger text-danger ml-2 font-weight-bold">
                    {{ $items->count() }} Item(s) Short
                </span>
            </h6>

            <div class="header-elements">
                <span class="text-muted font-size-sm font-weight-semibold mr-3">
                    Total Shortage: <strong class="text-danger">{{ (float)$totalShortageUnits == (int)$totalShortageUnits ? number_format($totalShortageUnits) : number_format($totalShortageUnits, 2) }} Units</strong>
                </span>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover datatable-button-html5-columns border-top-0 mb-0">
                    <thead class="bg-light font-size-xs text-uppercase">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Item Code</th>
                            <th>Item / Equipment Description</th>
                            <th>Register Type</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-center">On-Hand Qty</th>
                            <th class="text-center">Reorder Level</th>
                            <th class="text-center">Deficit / Qty Needed</th>
                            <th>Primary Supplier</th>
                            <th class="text-center no-export" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                        @php
                            $isSafety = $row->isSafetyStock();
                            $needed = max(0, (float)$row->low_stock - (float)$row->qty);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge badge-dark font-weight-bold font-size-xs px-2 py-1">
                                    {{ $row->item_code }}
                                </span>
                            </td>
                            <td>
                                <span class="font-weight-bold text-dark">{{ $row->name }}</span>
                            </td>
                            <td>
                                @if($isSafety)
                                    <span class="badge badge-danger font-weight-semibold px-2 py-1">
                                        <i class="icon-shield-check mr-1"></i> Worker Safety &amp; PPE
                                    </span>
                                @else
                                    <span class="badge badge-success font-weight-semibold px-2 py-1">
                                        <i class="icon-boxes mr-1"></i> Store Material / Part
                                    </span>
                                @endif
                            </td>
                            <td><span class="badge badge-light border px-2 py-1">{{ $row->category }}</span></td>
                            <td><span class="badge badge-secondary">{{ $row->unit }}</span></td>
                            <td class="text-center">
                                <span class="badge badge-danger font-weight-bold px-2 py-1">
                                    {{ (float)$row->qty == (int)$row->qty ? number_format($row->qty) : number_format($row->qty, 2) }}
                                </span>
                            </td>
                            <td class="text-center text-muted font-size-xs">
                                {{ (float)$row->low_stock == (int)$row->low_stock ? number_format($row->low_stock) : number_format($row->low_stock, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-warning text-dark font-weight-bold px-2 py-1">
                                    +{{ (float)$needed == (int)$needed ? number_format($needed) : number_format($needed, 2) }} {{ $row->unit }} Needed
                                </span>
                            </td>
                            <td>{{ $row->supplier ?: '—' }}</td>
                            <td class="text-center">
                                @if($isSafety)
                                    <a href="{{ route('materials.safety_stock') }}" class="btn btn-danger btn-xs font-weight-semibold">
                                        <i class="icon-plus2 mr-1"></i> Restock PPE
                                    </a>
                                @else
                                    <a href="{{ route('materials.index') }}" class="btn btn-primary btn-xs font-weight-semibold">
                                        <i class="icon-plus2 mr-1"></i> Restock
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted p-4">
                                <i class="icon-checkmark-circle text-success mr-2" style="font-size: 24px;"></i>
                                <div class="font-weight-semibold mt-1">All Stock Levels Sufficient</div>
                                <div class="font-size-xs">There are no items matching this criteria currently below safety reorder levels.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
