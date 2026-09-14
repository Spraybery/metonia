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
                <a href="{{ route('materials.restock_needed.print', request()->query()) }}" target="_blank" class="btn btn-emerald text-white font-weight-bold btn-sm shadow-xs mr-2">
                    <i class="icon-printer mr-1"></i> Print Restock Requisition
                </a>
                @if(Auth::user()->canEditRestockFinance())
                    <span class="badge badge-success font-size-xs px-2 py-1">
                        <i class="icon-coin-dollar mr-1"></i> Accountant Pricing Control Active
                    </span>
                @endif
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
    <div class="card mb-4">
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
                <span class="text-muted font-size-sm font-weight-semibold mr-3">
                    Est. Requisition Budget: <strong class="text-success font-weight-bold">KES {{ number_format($totalEstimatedBudget, 2) }}</strong>
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
                            <th class="text-right">Est. Unit Price (KES)</th>
                            <th class="text-right">Est. Budget Needed (KES)</th>
                            <th>Primary Supplier</th>
                            <th class="text-center no-export" style="min-width: 140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                        @php
                            $isSafety = $row->isSafetyStock();
                            $needed = max(0, (float)$row->low_stock - (float)$row->qty);
                            $estTotalCost = $needed * (float)$row->unit_cost;
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
                            <td class="text-right font-weight-semibold text-dark">
                                KES {{ number_format($row->unit_cost, 2) }}
                            </td>
                            <td class="text-right font-weight-bold text-success">
                                KES {{ number_format($estTotalCost, 2) }}
                            </td>
                            <td>{{ $row->supplier ?: '—' }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    @if(Auth::user()->canEditRestockFinance())
                                        <button type="button" class="btn btn-outline-success btn-xs font-weight-semibold mr-1" data-toggle="modal" data-target="#editPriceModal{{ $row->id }}" title="Enter / Update Financial Cost">
                                            <i class="icon-coin-dollar mr-1"></i> Edit Price
                                        </button>
                                    @endif

                                    @if($isSafety)
                                        <a href="{{ route('materials.safety_stock') }}" class="btn btn-danger btn-xs font-weight-semibold">
                                            <i class="icon-plus2 mr-1"></i> Restock
                                        </a>
                                    @else
                                        <a href="{{ route('materials.index') }}" class="btn btn-primary btn-xs font-weight-semibold">
                                            <i class="icon-plus2 mr-1"></i> Restock
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted p-4">
                                <i class="icon-checkmark-circle text-success mr-2" style="font-size: 24px;"></i>
                                <div class="font-weight-semibold mt-1">All Stock Levels Sufficient</div>
                                <div class="font-size-xs">There are no items matching this criteria currently below safety reorder levels.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($items->count() > 0)
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="8" class="text-right text-uppercase font-size-xs">Grand Total Requisition Estimate:</td>
                            <td class="text-center text-danger font-size-sm">
                                +{{ (float)$totalShortageUnits == (int)$totalShortageUnits ? number_format($totalShortageUnits) : number_format($totalShortageUnits, 2) }} Units
                            </td>
                            <td></td>
                            <td class="text-right text-success font-size-sm font-weight-bold">
                                KES {{ number_format($totalEstimatedBudget, 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Monthly Restock Expenditure Tracker Section (Accountant Financial Tracker) --}}
    <div class="card">
        <div class="card-header header-elements-inline bg-light">
            <h6 class="card-title font-weight-bold mb-0">
                <i class="icon-calendar3 text-primary mr-2"></i> Monthly Restock Expenditure Tracker (Money Used on Purchases)
            </h6>
            <div class="header-elements">
                <span class="badge badge-primary font-weight-semibold">
                    {{ $monthlyExpenditures->count() }} Month(s) Recorded
                </span>
            </div>
        </div>

        <div class="card-body">
            @if($monthlyExpenditures->count() > 0)
                <div class="row">
                    @foreach($monthlyExpenditures as $mIndex => $mExp)
                    <div class="col-md-6 col-xl-4 mb-3">
                        <div class="card card-body border-top-3 border-top-primary shadow-xs h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-dark font-size-lg">
                                    <i class="icon-calendar text-primary mr-1"></i> {{ $mExp['month_name'] }}
                                </span>
                                <span class="badge badge-light border text-muted font-weight-semibold">
                                    {{ $mExp['transaction_count'] }} Restock Batch(es)
                                </span>
                            </div>

                            <div class="my-2 p-2 bg-light rounded text-center">
                                <div class="text-muted font-size-xs text-uppercase font-weight-semibold">Total Money Used / Spent</div>
                                <div class="text-success font-weight-bold font-size-lg">
                                    KES {{ number_format($mExp['total_cost'], 2) }}
                                </div>
                                <div class="text-muted font-size-xs mt-1">
                                    Total Restocked Units: <strong>{{ number_format($mExp['total_units'], 2) }}</strong>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm font-weight-semibold mt-2" data-toggle="collapse" data-target="#monthlyDetails{{ $mIndex }}">
                                <i class="icon-list-unordered mr-1"></i> View Itemized Restocks
                            </button>

                            <div class="collapse mt-3" id="monthlyDetails{{ $mIndex }}">
                                <div class="table-responsive">
                                    <table class="table table-xs table-bordered font-size-xs">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Item</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-right">Unit Price</th>
                                                <th class="text-right">Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mExp['movements'] as $mv)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($mv->date)->format('M d') }}</td>
                                                <td><span class="font-weight-semibold">{{ $mv->material_name }}</span></td>
                                                <td class="text-center">{{ (float)$mv->qty }} {{ $mv->unit }}</td>
                                                <td class="text-right">KES {{ number_format($mv->unit_cost ?? ($mv->material->unit_cost ?? 0), 2) }}</td>
                                                <td class="text-right font-weight-bold text-success">KES {{ number_format($mv->total_cost, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted p-4">
                    <i class="icon-coin-dollar text-muted mb-2" style="font-size: 32px;"></i>
                    <div class="font-weight-semibold">No Restock Purchases Recorded Yet</div>
                    <div class="font-size-xs">As inventory items are restocked, monthly expenditure reports will be automatically computed here.</div>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Accountant Price Edit Modals (Rendered Outside Table for DataTables Compatibility) --}}
@if(Auth::user()->canEditRestockFinance())
    @foreach($items as $row)
    @php
        $needed = max(0, (float)$row->low_stock - (float)$row->qty);
    @endphp
    <div class="modal fade" id="editPriceModal{{ $row->id }}" tabindex="-1" role="dialog" aria-labelledby="editPriceModalLabel{{ $row->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content text-left">
                <form method="POST" action="{{ route('materials.update_restock_price', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-success text-white">
                        <h6 class="modal-title font-weight-bold" id="editPriceModalLabel{{ $row->id }}">
                            <i class="icon-coin-dollar mr-2"></i> Accountant Restock Price Estimation
                        </h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info border-0 font-size-sm py-2">
                            <i class="icon-info22 mr-1"></i> Enter the estimated unit price needed for <strong>{{ $row->name }}</strong> ({{ $row->item_code }}).
                        </div>
                        <div class="form-group mb-2">
                            <label class="font-weight-semibold">Item Name:</label>
                            <input type="text" class="form-control" value="{{ $row->name }}" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-semibold">Deficit Qty Needed:</label>
                                <input type="text" class="form-control" value="{{ (float)$needed == (int)$needed ? number_format($needed) : number_format($needed, 2) }} {{ $row->unit }}" readonly>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-semibold">Unit Price (KES): <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="unit_cost" class="form-control font-weight-bold" value="{{ old('unit_cost', $row->unit_cost) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-light font-weight-semibold" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="icon-checkmark-circle mr-1"></i> Save Estimated Price
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endif

@endsection
