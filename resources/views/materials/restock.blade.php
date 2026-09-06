@extends('layouts.master')
@section('page_title', 'Supplier Restock & Delivery Data')

@section('content')
<div class="content">

    {{-- Header Action & Page Overview --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 12px;">
        <div>
            <h5 class="font-weight-bold mb-1 text-dark">
                <i class="icon-arrow-down5 text-success mr-2"></i> Supplier Restock &amp; Delivery Register
            </h5>
            <p class="text-muted mb-0 font-size-sm">
                Track incoming stock deliveries from vendors and record inventory restock consignments.
            </p>
        </div>
        <div>
            <a href="{{ route('materials.restock.print', request()->query()) }}" target="_blank" class="btn btn-light font-weight-semibold shadow-xs mr-1">
                <i class="icon-printer mr-1"></i> Print Register
            </a>
            @if(Auth::user()->canEdit('materials'))
            <button type="button" class="btn btn-primary font-weight-semibold shadow-xs mr-1" data-toggle="modal" data-target="#modal-add-item">
                <i class="icon-plus2 mr-1"></i> Add Item
            </button>
            <button type="button" class="btn btn-success font-weight-semibold shadow-xs" data-toggle="modal" data-target="#modal-supplier-restock">
                <i class="icon-arrow-down5 mr-1"></i> Record Supplier Restock
            </button>
            @endif
        </div>
    </div>

    {{-- Summary Stats Bar --}}
    <div class="row mb-3">
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Total Restock Deliveries</div>
                <div class="h4 font-weight-bold text-success mb-0">{{ number_format($restockMovements->count()) }}</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Unique Suppliers Recorded</div>
                <div class="h4 font-weight-bold text-primary mb-0">{{ number_format($materials->pluck('supplier')->filter()->unique()->count()) }}</div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-2">
            <div class="bg-white border rounded p-3 shadow-xs">
                <div class="text-muted font-size-xs font-weight-semibold text-uppercase">Catalog Items Available</div>
                <div class="h4 font-weight-bold text-dark mb-0">{{ number_format($materials->count()) }}</div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="card border">
        <div class="card-header bg-light">
            <h6 class="card-title font-weight-bold mb-0">
                <i class="icon-list mr-2 text-success"></i> Incoming Supplier Consignments Log
            </h6>
        </div>

        <div class="card-body">
            {{-- Filter Bar --}}
            <div class="mb-3 p-2 bg-light border rounded">
                <form action="{{ route('materials.restock') }}" method="GET" class="form-inline d-flex flex-wrap" style="gap: 8px;">
                    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="Search material, supplier, manager or note..." value="{{ request('search') }}" style="min-width: 280px;">
                    <button type="submit" class="btn btn-light btn-sm font-weight-semibold">Filter Restocks</button>
                    @if(request('search'))
                        <a href="{{ route('materials.restock') }}" class="btn btn-link btn-sm text-danger">Reset</a>
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
                            <th>Material Description</th>
                            <th class="text-center">Quantity Received</th>
                            <th>Supplier / Vendor Name</th>
                            <th>Received By</th>
                            <th>Delivery Date</th>
                            <th>Note / Consignment Ref</th>
                            @if(Auth::user()->canEdit('materials') || Auth::user()->canDelete())
                            <th class="text-center no-export" style="width: 80px;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($restockMovements as $m)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge badge-dark font-weight-bold font-size-xs px-2 py-1" style="letter-spacing: 0.5px;">
                                    {{ $m->material?->item_code ?: ('MAT-' . str_pad($m->material_id, 4, '0', STR_PAD_LEFT)) }}
                                </span>
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->material_name }}</span>
                            </td>
                            <td class="text-center font-weight-bold text-success">
                                +{{ number_format($m->qty, 2) }} {{ $m->unit }}
                            </td>
                            <td>
                                <span class="font-weight-semibold text-primary">{{ $m->material?->supplier ?: ($m->note ? Str::after($m->note, 'Supplier: ') : 'Apex Steel Kenya Ltd') }}</span>
                            </td>
                            <td>
                                <span class="font-weight-semibold text-dark">{{ $m->issued_by ?: ($m->person ?: 'Grace Nduta') }}</span>
                            </td>
                            <td class="font-size-sm text-muted">
                                {{ $m->date ? $m->date->format('d M Y') : $m->created_at->format('d M Y') }}
                            </td>
                            <td class="font-size-sm text-muted">
                                {{ $m->note ?: 'Supplier consignment verified & received into stock.' }}
                            </td>
                            @if(Auth::user()->canEdit('materials') || Auth::user()->canDelete())
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a href="#" class="list-icons-item" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            @if(Auth::user()->canEdit('materials'))
                                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#modal-edit-restock-{{ $m->id }}">
                                                <i class="icon-pencil text-success"></i> Edit Restock
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ route('materials.movement.destroy', $m->id) }}" id="del-restock-{{ $m->id }}">
                                                @csrf @method('DELETE')
                                            </form>
                                            <a href="#" onclick="if(confirm('Delete restock record of {{ $m->material_name }}? Received stock quantity will be deducted.')) { document.getElementById('del-restock-{{ $m->id }}').submit(); }" class="dropdown-item text-danger">
                                                <i class="icon-trash text-danger"></i> Delete Restock
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Restock Modal --}}
                                @if(Auth::user()->canEdit('materials'))
                                <div id="modal-edit-restock-{{ $m->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-left">
                                            <div class="modal-header bg-success text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="icon-pencil mr-1"></i> Edit Restock Delivery: {{ $m->material_name }}
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('materials.movement.update', $m->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Material Description</label>
                                                        <input type="text" class="form-control" value="{{ $m->material_name }}" disabled>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Supplier / Vendor Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="supplier" class="form-control" value="{{ $m->material?->supplier ?: 'Apex Steel Kenya Ltd' }}" required>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="col-6 form-group">
                                                            <label class="font-weight-semibold">Quantity Received <span class="text-danger">*</span></label>
                                                            <input type="number" step="0.01" min="0.01" name="qty" class="form-control" value="{{ $m->qty }}" required>
                                                        </div>
                                                        <div class="col-6 form-group">
                                                            <label class="font-weight-semibold">Delivery Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="date" class="form-control" value="{{ $m->date ? $m->date->format('Y-m-d') : date('Y-m-d') }}" required>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Receiving Manager / Staff <span class="text-danger">*</span></label>
                                                        <input type="text" name="issued_by" class="form-control" value="{{ $m->issued_by ?: ($m->person ?: Auth::user()->name) }}" required>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="font-weight-semibold">Delivery Note / Invoice Note</label>
                                                        <textarea name="note" class="form-control" rows="2" placeholder="Delivery note details...">{{ $m->note }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success font-weight-semibold">Save Restock Corrections</button>
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
                            <td colspan="8" class="text-center text-muted p-4">
                                No supplier restock deliveries logged in the register yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Manager Modal: Restock from Supplier --}}
<div id="modal-supplier-restock" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-arrow-down5 mr-2"></i> Record Supplier Delivery / Stock In
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
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="font-weight-semibold mb-0">Select Store Material <span class="text-danger">*</span></label>
                            @if(Auth::user()->canEdit('materials'))
                            <a href="#" data-toggle="modal" data-target="#modal-add-item" data-dismiss="modal" class="text-primary font-size-xs font-weight-semibold">
                                <i class="icon-plus2 mr-1"></i> Add New Item
                            </a>
                            @endif
                        </div>
                        <select id="restock-mat-select" class="form-control select-search" required onchange="updateRestockAction(this.value)">
                            <option value="">-- Choose Store Item --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" data-supplier="{{ $m->supplier }}">
                                    {{ $m->name }} (Current Stock: {{ $m->qty }} {{ $m->unit }})
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

{{-- Modal Add New Store Item --}}
@if(Auth::user()->canEdit('materials'))
<div id="modal-add-item" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title font-weight-bold">
                    <i class="icon-plus2 mr-2"></i> Register New Store Inventory Item
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('materials.store') }}" method="POST">
                @csrf
                <input type="hidden" name="unit_cost" value="0.00">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-semibold">Material / Item Description <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Mild Steel Sheet 3mm x 4ft x 8ft" required>
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
                            <label class="font-weight-semibold">Unit Quantity Measure <span class="text-danger">*</span></label>
                            <select name="unit" class="form-control" required>
                                @foreach($units as $u)
                                    <option value="{{ $u }}" {{ $u === 'Pieces' ? 'selected' : '' }}>{{ $u }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Initial Stock On-Hand <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="qty" class="form-control" value="0.00" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="font-weight-semibold">Reorder Alert Threshold <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="low_stock" class="form-control" value="5.00" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Primary Supplier / Vendor Name</label>
                        <input type="text" name="supplier" class="form-control" placeholder="e.g. Apex Steel Kenya Ltd">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Register Item in Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    function updateRestockAction(matId) {
        if (matId) {
            document.getElementById('restock-movement-form').action = '/materials/' + matId + '/movement';
            var opt = document.querySelector('#restock-mat-select option[value="' + matId + '"]');
            if (opt && opt.dataset.supplier) {
                document.getElementById('restock-supplier-input').value = opt.dataset.supplier;
            }
        }
    }
</script>
@endpush

@endsection
