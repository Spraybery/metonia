@extends('layouts.master')
@section('page_title', 'Worker Safety & Personal Protective Equipment (PPE) Register')

@section('content')
<div class="content">

    {{-- Header Action & Page Overview --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 12px;">
        <div>
            <h5 class="font-weight-bold mb-1 text-dark">
                <i class="icon-shield-check text-primary mr-2"></i> Worker Safety &amp; Personal Protective Equipment (PPE) Register
            </h5>
            <p class="text-muted mb-0 font-size-sm">
                Track workshop safety gear, worker protective equipment inventory, staff issuance, and PPE reorder thresholds.
            </p>
        </div>
        <div>
            <a href="{{ route('materials.safety_stock.print', request()->query()) }}" target="_blank" class="btn btn-light font-weight-semibold shadow-xs mr-1">
                <i class="icon-printer mr-1"></i> Print Register
            </a>
            @if(Auth::user()->canEdit('materials'))
            <button type="button" class="btn btn-primary font-weight-semibold shadow-xs mr-1" data-toggle="modal" data-target="#modal-add-safety-item">
                <i class="icon-plus2 mr-1"></i> Add Item
            </button>
            <button type="button" class="btn btn-success font-weight-semibold shadow-xs" data-toggle="modal" data-target="#modal-restock-safety">
                <i class="icon-arrow-down5 mr-1"></i> Restock Safety Gear
            </button>
            @endif
        </div>
    </div>

    {{-- Summary Stats Bar --}}
    <div class="row mb-3">
        <div class="col-md-6 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Catalog Safety Gear SKUs</div>
                <div class="h4 font-weight-bold text-primary mb-0">{{ number_format($totalSafetyItems) }}</div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">PPE Low-Stock Reorder Alerts</div>
                <div class="h4 font-weight-bold {{ $lowStockSafetyItems > 0 ? 'text-danger' : 'text-success' }} mb-0">
                    {{ number_format($lowStockSafetyItems) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card border">
        <div class="card-header bg-light">
            <h6 class="card-title font-weight-bold mb-0">
                <i class="icon-shield-notice mr-2 text-primary"></i> Worker Protective Equipment (PPE) Inventory Log
            </h6>
        </div>

        <div class="card-body">
            {{-- Search Bar --}}
            <div class="mb-3 p-2 bg-light border rounded">
                <form action="{{ route('materials.safety_stock') }}" method="GET" class="form-inline d-flex flex-wrap" style="gap: 8px;">
                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search safety gear or vendor..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-light btn-sm font-weight-semibold">Filter Equipment</button>
                    @if(request('search'))
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
                            <th style="width: 120px;">Item Code</th>
                            <th>Safety Equipment / PPE Description</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-center">On-Hand Qty</th>
                            <th class="text-center">Safety Reorder Level</th>
                            <th class="text-center">Stock Status</th>
                            <th>Primary Supplier</th>
                            <th class="text-center no-export" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $row)
                        @php
                            $isDepleted = (float) $row->qty <= 0;
                            $isLow = $row->isLowStock();
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge badge-dark font-weight-bold font-size-xs px-2 py-1" style="letter-spacing: 0.5px;">
                                    {{ $row->item_code }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('materials.movements', $row->id) }}" class="font-weight-bold text-dark">
                                    {{ $row->name }}
                                </a>
                            </td>
                            <td><span class="badge badge-primary px-2 py-1">{{ $row->category }}</span></td>
                            <td><span class="badge badge-secondary">{{ $row->unit }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $isDepleted ? 'badge-danger' : ($isLow ? 'badge-warning' : 'badge-success') }} font-weight-bold px-2 py-1">
                                    {{ (float)$row->qty == (int)$row->qty ? number_format($row->qty) : number_format($row->qty, 2) }}
                                </span>
                            </td>
                            <td class="text-center text-muted font-size-xs">
                                {{ (float)$row->low_stock == (int)$row->low_stock ? number_format($row->low_stock) : number_format($row->low_stock, 2) }}
                            </td>
                            <td class="text-center">
                                @if($isDepleted)
                                    <span class="badge badge-danger font-weight-bold px-2">
                                        <i class="icon-cross2 mr-1"></i> Out of Stock
                                    </span>
                                @elseif($isLow)
                                    <span class="badge badge-warning text-dark font-weight-bold px-2">
                                        <i class="icon-warning mr-1"></i> Low Stock Alert
                                    </span>
                                @else
                                    <span class="badge badge-success font-weight-bold px-2">
                                        <i class="icon-checkmark mr-1"></i> In Stock
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
                                                <i class="icon-history text-primary"></i> View Issuance &amp; Stock Audit
                                            </a>
                                            @if(Auth::user()->canEdit('materials'))
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-issue-worker-{{ $row->id }}">
                                                <i class="icon-arrow-up5 text-danger"></i> Issue to Worker / Staff
                                            </a>
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-restock-{{ $row->id }}">
                                                <i class="icon-arrow-down5 text-success"></i> Restock Safety Gear
                                            </a>
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-{{ $row->id }}">
                                                <i class="icon-pencil text-muted"></i> Edit Item Details
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Issue to Worker Modal --}}
                                @if(Auth::user()->canEdit('materials'))
                                <div id="modal-issue-worker-{{ $row->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog text-left">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="icon-arrow-up5 mr-2"></i> Issue Safety Gear: {{ $row->name }}
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('materials.movement', $row->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="type" value="out">
                                                <div class="modal-body">
                                                    <div class="alert alert-info py-2 font-size-sm">
                                                        <i class="icon-info22 mr-1"></i> Available On-Hand: <strong>{{ (float)$row->qty == (int)$row->qty ? number_format($row->qty) : number_format($row->qty, 2) }} {{ $row->unit }}</strong>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Quantity to Issue ({{ $row->unit }}) <span class="text-danger">*</span></label>
                                                        <input type="number" step="1" min="1" max="{{ (int)$row->qty }}" name="qty" class="form-control" value="1" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Worker / Technician Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="issued_to" class="form-control" required placeholder="e.g. Eng. Martin Kariuki (Welder)">
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Issued By / Store Supervisor</label>
                                                        <input type="text" name="issued_by" class="form-control" value="{{ Auth::user()->name }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Usage / Workstation Notes</label>
                                                        <input type="text" name="note" class="form-control" placeholder="e.g. Stage 3 Chassis Welding Safety Gear">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger font-weight-semibold">
                                                        <i class="icon-checkmark mr-1"></i> Issue Safety Gear
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Restock Modal --}}
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
                                                        <i class="icon-info22 mr-1"></i> Current On-Hand: <strong>{{ (float)$row->qty == (int)$row->qty ? number_format($row->qty) : number_format($row->qty, 2) }} {{ $row->unit }}</strong>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Restock Quantity ({{ $row->unit }}) <span class="text-danger">*</span></label>
                                                        <input type="number" step="1" min="1" name="qty" class="form-control" value="10" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Received By / Store Keeper</label>
                                                        <input type="text" name="issued_by" class="form-control" value="{{ Auth::user()->name }}">
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Supplier Consignment Notes</label>
                                                        <input type="text" name="note" class="form-control" placeholder="e.g. Received from {{ $row->supplier ?: 'Safety Vendor' }}">
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

                                {{-- Edit Modal --}}
                                <div id="modal-edit-{{ $row->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog text-left">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="icon-pencil mr-2"></i> Edit Safety Equipment Details
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('materials.update', $row->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="unit_cost" value="0.00">
                                                <div class="modal-body">
                                                    <div class="form-row">
                                                        <div class="col-md-5 form-group">
                                                            <label class="font-weight-semibold">Item Code / SKU</label>
                                                            <input type="text" name="item_code" class="form-control" value="{{ $row->item_code }}" placeholder="e.g. SAF-0005">
                                                        </div>
                                                        <div class="col-md-7 form-group">
                                                            <label class="font-weight-semibold">Equipment Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ $row->name }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Category <span class="text-danger">*</span></label>
                                                            <select name="category" class="form-control" required>
                                                                @foreach($categories as $c)
                                                                    <option value="{{ $c }}" {{ $row->category === $c ? 'selected' : '' }}>{{ $c }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Unit Quantity Measure <span class="text-danger">*</span></label>
                                                            <select name="unit" class="form-control" required>
                                                                @foreach($units as $u)
                                                                    <option value="{{ $u }}" {{ $row->unit === $u ? 'selected' : '' }}>{{ $u }}</option>
                                                                @endforeach
                                                            </select>
                                                            <small class="form-text text-muted">Choose unit: e.g. <strong>Pieces</strong> (Helmets, Vests) or <strong>Pairs</strong> (Gloves, Boots).</small>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Safety Reorder Threshold <span class="text-danger">*</span></label>
                                                        <input type="number" step="1" name="low_stock" class="form-control" value="{{ (int)$row->low_stock }}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Primary Safety Vendor / Supplier</label>
                                                        <input type="text" name="supplier" class="form-control" value="{{ $row->supplier }}">
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
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted p-4">
                                No worker safety equipment recorded in inventory yet. Click <strong>Add Safety Item</strong> to register safety gear.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Modal Add Safety Item --}}
@if(Auth::user()->canEdit('materials'))
<div id="modal-add-safety-item" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-plus2 mr-2"></i> Register New Worker Safety Equipment / PPE
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('materials.store') }}" method="POST">
                @csrf
                <input type="hidden" name="unit_cost" value="0.00">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="col-md-5 form-group">
                            <label class="font-weight-semibold">Item Code / SKU</label>
                            <input type="text" name="item_code" class="form-control" placeholder="e.g. SAF-0005">
                            <small class="form-text text-muted">Auto-generated if left blank</small>
                        </div>
                        <div class="col-md-7 form-group">
                            <label class="font-weight-semibold">Equipment / PPE Description <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. EN388 Heavy Leather Welding Gloves" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="Worker Safety & PPE" selected>Worker Safety &amp; PPE</option>
                                <option value="Reflecting & Safety">Reflecting &amp; Safety</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Unit Quantity Measure <span class="text-danger">*</span></label>
                            <select name="unit" class="form-control" required>
                                @foreach($units as $u)
                                    <option value="{{ $u }}" {{ $u === 'Pairs' ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Choose unit: e.g. <strong>Pieces</strong> (Helmets, Vests) or <strong>Pairs</strong> (Gloves, Boots).</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Initial Stock On-Hand <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="0" name="qty" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Safety Reorder Threshold <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="0" name="low_stock" class="form-control" value="5" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Primary Safety Vendor / Supplier</label>
                        <input type="text" name="supplier" class="form-control" placeholder="e.g. Safety Plus East Africa Ltd">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Register Safety Equipment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Restock Safety Gear --}}
<div id="modal-restock-safety" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-arrow-down5 mr-2"></i> Restock Worker Safety Equipment
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('materials.restock') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-semibold mb-0">Select Safety Equipment Item <span class="text-danger">*</span></label>
                            <a href="#" data-toggle="modal" data-target="#modal-add-safety-item" data-dismiss="modal" class="text-primary font-size-xs font-weight-semibold">
                                <i class="icon-plus2 mr-1"></i> Add New Item
                            </a>
                        </div>
                        <select name="material_id" class="form-control select-search" required>
                            <option value="">-- Select Safety Item --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->name }} (Current: {{ (float)$m->qty == (int)$m->qty ? number_format($m->qty) : number_format($m->qty, 2) }} {{ $m->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Quantity Restocked <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="1" name="qty" class="form-control" value="10" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Received By / Store Keeper</label>
                        <input type="text" name="issued_by" class="form-control" value="{{ Auth::user()->name }}">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Supplier / Consignment Details</label>
                        <input type="text" name="note" class="form-control" placeholder="e.g. Consignment Delivery from Safety Vendor">
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
@endif

@endsection
