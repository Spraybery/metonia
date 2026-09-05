@extends('layouts.master')
@section('page_title', 'Register New Vehicle Job Card')

@section('content')
<div class="content">

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-plus2 mr-2 text-primary"></i> Open New Assembly / Maintenance Job Card
            </h6>
            <div class="header-elements">
                <a href="{{ route('vehicles.index') }}" class="btn btn-light btn-sm mr-2">
                    <i class="icon-arrow-left7 mr-1"></i> Back to Register
                </a>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('vehicles.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <fieldset>
                            <legend class="font-weight-semibold text-uppercase font-size-sm border-bottom pb-1 mb-3">
                                <i class="icon-truck mr-2"></i> Vehicle Identification
                            </legend>

                            <div class="form-group">
                                <label class="font-weight-semibold">Plate / Chassis / VIN <span class="text-danger">*</span></label>
                                <input type="text" name="plate" class="form-control text-uppercase" placeholder="e.g. MET-2026-8849102" value="{{ old('plate') }}" required>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Manufacturer / Make <span class="text-danger">*</span></label>
                                    <input type="text" name="make" class="form-control" value="{{ old('make', 'Metonia') }}" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Model Specification <span class="text-danger">*</span></label>
                                    <input type="text" name="model" class="form-control" placeholder="e.g. Titan 4x4 Heavy Hauler" value="{{ old('model') }}" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Manufacture Year</label>
                                    <input type="text" name="year" class="form-control" placeholder="2026" maxlength="4" value="{{ old('year', date('Y')) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Initial Build Stage <span class="text-danger">*</span></label>
                                    <select name="stage" class="form-control" required>
                                        @foreach($stages as $st)
                                            <option value="{{ $st }}" {{ old('stage') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Assigned Lead Supervisor</label>
                                <select name="assigned_to" class="form-control select-search">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($supervisors as $sup)
                                        <option value="{{ $sup->name }}" {{ old('assigned_to') === $sup->name ? 'selected' : '' }}>
                                            {{ $sup->name }} ({{ $sup->title }} - {{ $sup->stage }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </fieldset>
                    </div>

                    <div class="col-md-6">
                        <fieldset>
                            <legend class="font-weight-semibold text-uppercase font-size-sm border-bottom pb-1 mb-3">
                                <i class="icon-user mr-2"></i> Client Account &amp; Financials
                            </legend>

                            <div class="form-group">
                                <label class="font-weight-semibold">Customer / Account Name</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="e.g. Trans-East Logistics Ltd" value="{{ old('customer_name') }}">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Customer Contact Phone</label>
                                <input type="text" name="customer_phone" class="form-control" placeholder="+254 700 000 000" value="{{ old('customer_phone') }}">
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Estimated Labor Cost (KES)</label>
                                    <input type="number" step="0.01" name="labor_cost" class="form-control" placeholder="0.00" value="{{ old('labor_cost', '0.00') }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Billable Invoice Total (KES)</label>
                                    <input type="number" step="0.01" name="invoice_total" class="form-control" placeholder="0.00" value="{{ old('invoice_total', '0.00') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Diagnostic / Intake Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Technical diagnostic remarks, customer requests, or intake condition notes...">{{ old('notes') }}</textarea>
                            </div>
                        </fieldset>
                    </div>
                </div>

                {{-- Action Buttons (Section 7 Standard) --}}
                <div class="text-right mt-3 border-top pt-3">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-light mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Register Job Card
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
