@extends('layouts.master')
@section('page_title', 'Edit Job Card: ' . $vehicle->plate)

@section('content')
<div class="content">

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">
                <i class="icon-pencil mr-2 text-primary"></i> Edit Specifications — #{{ $vehicle->plate }}
            </h6>
            <div class="header-elements">
                <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-light btn-sm mr-2">
                    <i class="icon-arrow-left7 mr-1"></i> Back to Job Card
                </a>
                {!! Qs::getPanelOptions() !!}
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <fieldset>
                            <legend class="font-weight-semibold text-uppercase font-size-sm border-bottom pb-1 mb-3">
                                <i class="icon-truck mr-2"></i> Vehicle Identification
                            </legend>

                            <div class="form-group">
                                <label class="font-weight-semibold">Plate / Chassis / VIN <span class="text-danger">*</span></label>
                                <input type="text" name="plate" class="form-control text-uppercase" value="{{ old('plate', $vehicle->plate) }}" required>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Manufacturer / Make <span class="text-danger">*</span></label>
                                    <input type="text" name="make" class="form-control" value="{{ old('make', $vehicle->make) }}" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Model Specification <span class="text-danger">*</span></label>
                                    <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model) }}" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Manufacture Year</label>
                                    <input type="text" name="year" class="form-control" maxlength="4" value="{{ old('year', $vehicle->year) }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-semibold">Build Stage <span class="text-danger">*</span></label>
                                    <select name="stage" class="form-control" required>
                                        @foreach($stages as $st)
                                            <option value="{{ $st }}" {{ old('stage', $vehicle->stage) === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Assigned Lead Supervisor</label>
                                <select name="assigned_to" class="form-control select-search">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($supervisors as $sup)
                                        <option value="{{ $sup->name }}" {{ old('assigned_to', $vehicle->assigned_to) === $sup->name ? 'selected' : '' }}>
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
                                <i class="icon-user mr-2"></i> Client Account Information
                            </legend>

                            <div class="form-group">
                                <label class="font-weight-semibold">Customer / Account Name</label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $vehicle->customer_name) }}">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Customer Contact Phone</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $vehicle->customer_phone) }}">
                                <input type="hidden" name="labor_cost" value="{{ $vehicle->labor_cost }}">
                                <input type="hidden" name="invoice_total" value="{{ $vehicle->invoice_total }}">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-semibold">Diagnostic / Intake Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $vehicle->notes) }}</textarea>
                            </div>
                        </fieldset>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="text-right mt-3 border-top pt-3">
                    <a href="{{ route('vehicles.show', $vehicle->id) }}" class="btn btn-light mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary font-weight-semibold">
                        <i class="icon-checkmark mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
