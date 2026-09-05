@extends('layouts.master')
@section('page_title', 'Safety Stock & Reorder Alerts Register')

@section('content')
<div class="content">

    {{-- Header Action & Page Overview --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 12px;">
        <div>
            <h5 class="font-weight-bold mb-1 text-dark">
                <i class="icon-warning2 text-warning mr-2"></i> Safety Stock &amp; Reorder Threshold Register
            </h5>
            <p class="text-muted mb-0 font-size-sm">
                Monitor low-stock items at or below safety thresholds and manage stock replenishment requirements.
            </p>
        </div>
        <div>
            <a href="{{ route('materials.restock') }}" class="btn btn-success font-weight-semibold shadow-xs">
                <i class="icon-arrow-down5 mr-1"></i> Record Supplier Restock
            </a>
            <a href="{{ route('materials.issuance') }}" class="btn btn-outline-danger font-weight-semibold ml-1">
                <i class="icon-arrow-up5 mr-1"></i> Outward Material Issuance
            </a>
            <a href="{{ route('materials.index') }}" class="btn btn-light ml-1 font-weight-semibold">
                <i class="icon-boxes mr-1"></i> Store Catalog
            </a>
        </div>
    </div>

    {{-- Summary Stats Bar --}}
    <div class="row mb-3">
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Total Low-Stock Items</div>
                <div class="h4 font-weight-bold {{ $lowStockCount > 0 ? 'text-warning' : 'text-success' }} mb-0">
                    {{ number_format($lowStockCount) }}
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Zero-Stock / Depleted</div>
                <div class="h4 font-weight-bold {{ $outOfStockCount > 0 ? 'text-danger' : 'text-success' }} mb-0">
                    {{ number_format($outOfStockCount) }}
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Critical Deficit Items</div>
                <div class="h4 font-weight-bold {{ $criticalDeficitCount > 0 ? 'text-danger' : 'text-success' }} mb-0">
                    {{ number_format($criticalDeficitCount) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card border">
        <div class="card-header header-elements-inline bg-light">
            <h6 class="card-title font-weight-bold">
                <i class="icon-shield-notice mr-2 text-warning"></i> Safety Stock Alerts &amp; Reorder List
            </h6>
            <div class="header-elements">
                <button onclick="window.print()" class="btn btn-light btn-sm mr-2">
                    <i class="icon-printer mr-1"></i> Print Alert Report
                </button>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            {{-- Category Filter Bar --}}
            <div class="mb-3 p-2 bg-light border rounded">
                <form action="{{ route('materials.safety_stock') }}" method="GET" class="form-inline d-flex flex-wrap" style="gap: 8px;">
                    <label class="font-weight-semibold mr-2">Category Filter:</label>
                    <select name="category" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search item or supplier..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-light btn-sm font-weight-semibold">Filter Alerts</button>
                    @if(request('category') || request('search'))
                        <a href="{{ route('materials.safety_stock') }}" class="btn btn-link btn-sm text-danger">Reset</a>
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
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-center">On-Hand Qty</th>
                            <th class="text-center">Safety Threshold</th>
                            <th class="text-center">Shortage Deficit</th>
                            <th class="text-center">Status</th>
                            <th>Supplier</th>
                            <th class="text-center" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $row)
                        @php
                            $deficit = max(0, (float) $row->low_stock - (float) $row->qty);
                            $isDepleted = (float) $row->qty <= 0;
                            $isCritical = (float) $row->qty <= ((float) $row->low_stock / 2);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('materials.movements', $row->id) }}" class="font-weight-bold text-dark">
                                    {{ $row->name }}
                                </a>
                            </td>
                            <td><span class="badge badge-light border">{{ $row->category }}</span></td>
                            <td><span class="badge badge-secondary">{{ $row->unit }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $isDepleted ? 'badge-danger' : 'badge-warning' }} font-weight-bold px-2 py-1">
                                    {{ number_format($row->qty, 2) }}
                                </span>
                            </td>
                            <td class="text-center font-weight-semibold text-dark">
                                {{ number_format($row->low_stock, 2) }}
                            </td>
                            <td class="text-center font-weight-bold text-danger">
                                -{{ number_format($deficit, 2) }} {{ $row->unit }}
                            </td>
                            <td class="text-center">
                                @if($isDepleted)
                                    <span class="badge badge-danger font-weight-bold px-2">
                                        <i class="icon-cross2 mr-1"></i> Out of Stock
                                    </span>
                                @elseif($isCritical)
                                    <span class="badge badge-danger px-2">
                                        <i class="icon-warning mr-1"></i> Critical Low
                                    </span>
                                @else
                                    <span class="badge badge-warning text-dark px-2">
                                        <i class="icon-warning2 mr-1"></i> Below Threshold
                                    </span>
                                @endif
                            </td>
                            <td class="font-size-sm">{{ $row->supplier ?: '—' }}</td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="{{ route('materials.movements', $row->id) }}" class="dropdown-item">
                                                <i class="icon-history text-primary"></i> View Movement Audit
                                            </a>
                                            @if(Auth::user()->canEdit('materials'))
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-restock-{{ $row->id }}">
                                                <i class="icon-arrow-down5 text-success"></i> Restock Material
                                            </a>
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-threshold-{{ $row->id }}">
                                                <i class="icon-pencil text-muted"></i> Edit Safety Threshold
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Restock Modal --}}
                                @if(Auth::user()->canEdit('materials'))
                                <div id="modal-restock-{{ $row->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog text-left">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="icon-arrow-down5 mr-2"></i> Restock {{ $row->name }}
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('materials.movement', $row->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="type" value="in">
                                                <div class="modal-body">
                                                    <div class="alert alert-info py-2 font-size-sm">
                                                        <i class="icon-info22 mr-1"></i> Current On-Hand: <strong>{{ number_format($row->qty, 2) }} {{ $row->unit }}</strong> | Safety Threshold: <strong>{{ number_format($row->low_stock, 2) }} {{ $row->unit }}</strong>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Restock Quantity ({{ $row->unit }}) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" min="0.01" name="qty" class="form-control" value="{{ number_format($deficit > 0 ? $deficit : 10, 2, '.', '') }}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Received By / Staff</label>
                                                        <input type="text" name="issued_by" class="form-control" value="{{ Auth::user()->name }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Supplier / Consignment Notes</label>
                                                        <input type="text" name="note" class="form-control" placeholder="e.g. Delivery Batch #{{ date('Ym') }}-01 from {{ $row->supplier ?: 'Vendor' }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success font-weight-semibold">
                                                        <i class="icon-checkmark mr-1"></i> Add Stock Quantity
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Edit Threshold Modal --}}
                                <div id="modal-edit-threshold-{{ $row->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog text-left">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="icon-pencil mr-2"></i> Update Safety Threshold: {{ $row->name }}
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('materials.update', $row->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="name" value="{{ $row->name }}">
                                                <input type="hidden" name="category" value="{{ $row->category }}">
                                                <input type="hidden" name="unit" value="{{ $row->unit }}">
                                                <input type="hidden" name="supplier" value="{{ $row->supplier }}">
                                                <input type="hidden" name="unit_cost" value="0.00">
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Safety Reorder Threshold ({{ $row->unit }}) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" min="0" name="low_stock" class="form-control" value="{{ $row->low_stock }}" required>
                                                        <span class="form-text text-muted">Stock falling below this quantity triggers safety alerts.</span>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary font-weight-semibold">Save Threshold</button>
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
                            <td colspan="10" class="text-center text-muted p-4">
                                <i class="icon-checkmark-circle text-success icon-2x d-block mb-2"></i>
                                All store materials and parts are currently above safety stock reorder thresholds!
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
