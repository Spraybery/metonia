@extends('layouts.master')
@section('page_title', 'Store Inventory & Raw Materials')

@section('content')
<div class="content">

    {{-- Summary KPI Stat Bars (Section 5 Standard) --}}
    <div class="row mb-3">
        <div class="col-md-6 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Total Catalog SKUs</div>
                <div class="h4 font-weight-bold text-dark mb-0">{{ number_format($materials->count()) }}</div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 mb-2">
            <div class="bg-light border rounded p-3 text-center">
                <div class="text-muted font-size-sm font-weight-semibold text-uppercase">Low-Stock Items</div>
                <div class="h4 font-weight-bold {{ $lowStockCount > 0 ? 'text-danger' : 'text-success' }} mb-0">
                    {{ number_format($lowStockCount) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-boxes mr-2 text-primary"></i> Store Materials &amp; Parts Register
            </h6>
            <div class="header-elements">
                <a href="{{ route('materials.issuance') }}" class="btn btn-outline-danger btn-sm font-weight-semibold mr-1">
                    <i class="icon-arrow-up5 mr-1"></i> Outward Material Issuance
                </a>
                <a href="{{ route('materials.restock') }}" class="btn btn-outline-success btn-sm font-weight-semibold mr-1">
                    <i class="icon-arrow-down5 mr-1"></i> Supplier Restock Data
                </a>
                @if(Auth::user()->canEdit('materials'))
                    <button type="button" class="btn btn-primary btn-sm mr-1 font-weight-semibold" data-toggle="modal" data-target="#modal-add-material">
                        <i class="icon-plus2 mr-1"></i> Add Material SKU
                    </button>
                @endif
                <button onclick="window.print()" class="btn btn-light btn-sm mr-2">
                    <i class="icon-printer mr-1"></i> Print Catalog
                </button>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            {{-- Category Filter Bar --}}
            <div class="mb-3 p-2 bg-light border rounded">
                <form action="{{ route('materials.index') }}" method="GET" class="form-inline d-flex flex-wrap" style="gap: 8px;">
                    <label class="font-weight-semibold mr-2">Category Filter:</label>
                    <select name="category" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search item or supplier..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-light btn-sm">Filter</button>
                    @if(request('category') || request('search'))
                        <a href="{{ route('materials.index') }}" class="btn btn-link btn-sm text-danger">Reset</a>
                    @endif
                </form>
            </div>


            {{-- Standard DataTables --}}
            <div class="table-responsive">
                <table class="table datatable-button-html5-columns table-striped table-hover">
                    <thead>
                        <tr class="bg-light">
                            <th style="width: 50px;">#</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="text-center">On Hand</th>
                            <th class="text-center">Reorder Level</th>
                            <th>Supplier</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($materials as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('materials.movements', $row->id) }}" class="font-weight-semibold text-dark">
                                    {{ $row->name }}
                                </a>
                                @if($row->isLowStock())
                                    <span class="badge badge-danger ml-1" title="Stock at or below reorder threshold!">
                                        <i class="icon-warning"></i> Low Stock
                                    </span>
                                @endif
                            </td>
                            <td><span class="badge badge-light border">{{ $row->category }}</span></td>
                            <td><span class="badge badge-secondary">{{ $row->unit }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ $row->isLowStock() ? 'badge-danger' : 'badge-success' }} font-weight-bold px-2 py-1">
                                    {{ number_format($row->qty, 2) }}
                                </span>
                            </td>
                            <td class="text-center text-muted font-size-xs">
                                {{ number_format($row->low_stock, 2) }}
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
                                                <i class="icon-history"></i> Movement Log
                                            </a>
                                            @if(Auth::user()->canEdit('materials'))
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-{{ $row->id }}">
                                                <i class="icon-pencil"></i> Edit Item
                                            </a>
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-stock-{{ $row->id }}">
                                                <i class="icon-transmission"></i> Quick Movement
                                            </a>
                                            @endif
                                            @if(Auth::user()->canDelete())
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('materials.destroy', $row->id) }}" id="del-mat-{{ $row->id }}">
                                                @csrf @method('DELETE')
                                            </form>
                                            <a href="#" onclick="if(confirm('Delete {{ $row->name }}?')) { document.getElementById('del-mat-{{ $row->id }}').submit(); }" class="dropdown-item text-danger">
                                                <i class="icon-trash text-danger"></i> Delete Item
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Modal for this item --}}
                                @if(Auth::user()->canEdit('materials'))
                                <div id="modal-edit-{{ $row->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-slate-800 text-white">
                                                <h6 class="modal-title font-weight-bold">Edit Item: {{ $row->name }}</h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('materials.update', $row->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Item Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $row->name }}" required>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Category <span class="text-danger">*</span></label>
                                                            <select name="category" class="form-control" required>
                                                                @foreach($categories as $cat)
                                                                    <option value="{{ $cat }}" {{ $row->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="font-weight-semibold">Unit of Measurement <span class="text-danger">*</span></label>
                                                            <select name="unit" class="form-control" required>
                                                                @foreach($units as $u)
                                                                    <option value="{{ $u }}" {{ $row->unit === $u ? 'selected' : '' }}>{{ $u }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Low-Stock Reorder Threshold <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" name="low_stock" class="form-control" value="{{ $row->low_stock }}" required>
                                                        <input type="hidden" name="unit_cost" value="{{ $row->unit_cost ?: '0.00' }}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Supplier Name</label>
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

                                {{-- Quick Movement Modal for this item --}}
                                <div id="modal-stock-{{ $row->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-slate-800 text-white">
                                                <h6 class="modal-title font-weight-bold">Stock Movement: {{ $row->name }}</h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('materials.movement', $row->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Movement Type <span class="text-danger">*</span></label>
                                                        <select name="type" class="form-control" required>
                                                            <option value="in">🟢 Stock In (Restock / Supplier Delivery)</option>
                                                            <option value="out">🔴 Stock Out (Issuance / Adjustment / Scrap)</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="col-6 form-group">
                                                            <label class="font-weight-semibold">Quantity ({{ $row->unit }}) <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" min="0.01" name="qty" class="form-control" required placeholder="0.00">
                                                        </div>
                                                        <div class="col-6 form-group">
                                                            <label class="font-weight-semibold">Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Person Responsible / Staff <span class="text-danger">*</span></label>
                                                        <input type="text" name="person" class="form-control" value="{{ Auth::user()->name }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Linked Job Card (Optional)</label>
                                                        <select name="vehicle_id" class="form-control">
                                                            <option value="">-- None / General Store Movement --</option>
                                                            @foreach($activeVehicles as $v)
                                                                <option value="{{ $v->id }}">{{ $v->plate }} — {{ $v->make }} {{ $v->model }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Reason / Movement Note</label>
                                                        <textarea name="note" class="form-control" rows="2" placeholder="e.g. Delivery note #9841 or emergency chassis reinforcement"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary font-weight-semibold">Record Movement</button>
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

    {{-- Outward Material Issuance Register Log Card --}}
    <div class="card mt-4 border">
        <div class="card-header header-elements-inline bg-light">
            <h6 class="card-title font-weight-bold">
                <i class="icon-arrow-up5 mr-2 text-danger"></i> Outward Store Material Issuance Register
            </h6>
            <div class="header-elements">
                <span class="badge badge-primary font-weight-bold">Store Keeper Issuance Register</span>
            </div>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped table-hover border">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Material Description</th>
                            <th class="text-center">Quantity</th>
                            <th>Vehicle Name / Destination</th>
                            <th>Issued By</th>
                            <th>Issued To</th>
                            <th>Date Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outwardMovements as $m)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->material_name }}</span>
                            </td>
                            <td class="text-center font-weight-bold text-danger">
                                -{{ number_format($m->qty, 2) }} {{ $m->unit }}
                            </td>
                            <td>
                                @if($m->vehicle_id)
                                    <a href="{{ route('vehicles.show', $m->vehicle_id) }}" class="font-weight-semibold text-primary">
                                        {{ $m->vehicle_label ?: ('Job Card #' . $m->vehicle_id) }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $m->vehicle_label ?: 'General Store Deduction' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->issued_by ?: 'David Omondi' }}</span>
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->issued_to ?: ($m->person ?: 'Eng. Peter Kimani') }}</span>
                            </td>
                            <td class="font-size-sm text-muted">
                                {{ $m->date ? $m->date->format('d M Y') : $m->created_at->format('d M Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted p-4">
                                No outward material issuances logged in the store register yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Add Material Modal --}}
<div id="modal-add-material" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate-800 text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-plus2 mr-2"></i> Register New Store Material / Consumable
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('materials.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-semibold">Item Name / Specification <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Heavy Duty Structural Steel Beam 100x50mm" required>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Store Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-control" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Unit of Measurement <span class="text-danger">*</span></label>
                            <select name="unit" class="form-control" required>
                                @foreach($units as $u)
                                    <option value="{{ $u }}">{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Initial Stock <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="qty" class="form-control" value="0.00" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Reorder Threshold <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="low_stock" class="form-control" value="5.00" required>
                            <input type="hidden" name="unit_cost" value="0.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Primary Supplier / Vendor</label>
                        <input type="text" name="supplier" class="form-control" placeholder="e.g. Apex Steel Kenya Ltd">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Add Material
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Global Stock Movement Modal --}}
<div id="modal-movement" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate-800 text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-transmission mr-2"></i> Record Store Stock Movement
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="global-movement-form" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-semibold">Select Store Material <span class="text-danger">*</span></label>
                        <select id="global-mat-select" class="form-control select-search" required onchange="updateMovementAction(this.value)">
                            <option value="">-- Choose Store Item --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->qty }} {{ $m->unit }} in stock)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Movement Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="in">🟢 Stock In (Supplier Delivery / Restock)</option>
                            <option value="out">🔴 Stock Out (Plant Issuance / Deduction)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="qty" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Movement Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Staff Member / Receiver <span class="text-danger">*</span></label>
                        <input type="text" name="person" class="form-control" value="{{ Auth::user()->name }}" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Linked Vehicle Job Card (Optional)</label>
                        <select name="vehicle_id" class="form-control">
                            <option value="">-- General Stock (No Job Card) --</option>
                            @foreach($activeVehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->plate }} — {{ $v->make }} {{ $v->model }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Movement Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Delivery reference or issuance context..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Submit Movement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Manager Modal: Restock from Supplier --}}
<div id="modal-supplier-restock" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-arrow-down5 mr-2"></i> Record Supplier Delivery / Stock In (Manager)
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="restock-movement-form" method="POST">
                @csrf
                <input type="hidden" name="type" value="in">
                <div class="modal-body">
                    <div class="alert alert-success py-2 font-size-sm">
                        <i class="icon-info22 mr-1"></i> Record incoming materials from suppliers and automatically update store inventory.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Select Store Material <span class="text-danger">*</span></label>
                        <select id="restock-mat-select" class="form-control select-search" required onchange="updateRestockAction(this.value)">
                            <option value="">-- Choose Store Item --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" data-supplier="{{ $m->supplier }}">
                                    {{ $m->name }} (Current: {{ $m->qty }} {{ $m->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Supplier / Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="supplier" id="restock-supplier-input" class="form-control" placeholder="e.g. Apex Steel Kenya Ltd" required>
                    </div>

                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Quantity Received <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="qty" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Receiving Manager / Staff <span class="text-danger">*</span></label>
                        <input type="text" name="person" class="form-control" value="{{ Auth::user()->name }}" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Delivery Note / Invoice Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="e.g. Delivery note #DN-8842, consignment checked and certified."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Receive Stock into Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Shopkeeper Modal: Issue Material Out of Store --}}
<div id="modal-issue-vehicle" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-arrow-up5 mr-2"></i> Issue Store Material Out of Inventory (Storekeeper)
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="issue-movement-form" method="POST">
                @csrf
                <input type="hidden" name="type" value="out">
                <div class="modal-body">
                    <div class="alert alert-info py-2 font-size-sm">
                        <i class="icon-info22 mr-1"></i> Record outward material issuance. Stock is immediately deducted and registered to the target vehicle's Job Card.
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Material Description <span class="text-danger">*</span></label>
                        <select id="issue-mat-select" class="form-control select-search" required onchange="updateIssueAction(this.value)">
                            <option value="">-- Select Material Description --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" {{ $m->qty <= 0 ? 'disabled' : '' }}>
                                    {{ $m->name }} (Available: {{ $m->qty }} {{ $m->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Vehicle Name / Destination <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-control" required id="issue-vehicle-select" onchange="autoFillTechnician(this)">
                            <option value="">-- Choose Target Vehicle --</option>
                            @foreach($activeVehicles as $v)
                                <option value="{{ $v->id }}" data-lead="{{ $v->assigned_to }}">
                                    {{ $v->plate }} — {{ $v->make }} {{ $v->model }} (Stage: {{ $v->stage }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="qty" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Date Materials Issued <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Issued By (Storekeeper) <span class="text-danger">*</span></label>
                            <input type="text" name="issued_by" class="form-control" value="{{ Auth::user()->name }}" required>
                        </div>
                        <div class="col-6 form-group">
                            <label class="font-weight-semibold">Issued To (Technician) <span class="text-danger">*</span></label>
                            <input type="text" name="issued_to" id="technician-person-input" class="form-control" placeholder="e.g. Eng. Peter Kimani" required>
                            <input type="hidden" name="person" id="person-fallback-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Issuance Purpose / Workshop Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="e.g. Chassis cross-member structural fabrication"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Confirm Outward Store Issuance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateMovementAction(matId) {
        if (matId) {
            document.getElementById('global-movement-form').action = '/materials/' + matId + '/movement';
        }
    }

    function updateRestockAction(matId) {
        if (matId) {
            document.getElementById('restock-movement-form').action = '/materials/' + matId + '/movement';
            var opt = document.querySelector('#restock-mat-select option[value="' + matId + '"]');
            if (opt && opt.dataset.supplier) {
                document.getElementById('restock-supplier-input').value = opt.dataset.supplier;
            }
        }
    }

    function updateIssueAction(matId) {
        if (matId) {
            document.getElementById('issue-movement-form').action = '/materials/' + matId + '/movement';
        }
    }

    function autoFillTechnician(selectElem) {
        var opt = selectElem.options[selectElem.selectedIndex];
        if (opt && opt.dataset.lead) {
            document.getElementById('technician-person-input').value = opt.dataset.lead;
        }
    }
</script>
@endpush

@endsection
