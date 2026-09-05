<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\MaterialMovement;
use App\Models\Supervisor;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use App\Models\VehicleStageHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['stageHistories', 'parts'])->orderByDesc('created_at');

        if ($request->filled('stage')) {
            $query->where('stage', $request->query('stage'));
        }

        if ($request->filled('search')) {
            $s = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($s) {
                $q->where('plate', 'like', $s)
                    ->orWhere('model', 'like', $s)
                    ->orWhere('customer_name', 'like', $s)
                    ->orWhere('assigned_to', 'like', $s);
            });
        }

        $vehicles = $query->get();
        $stages = Qs::getStages();
        $supervisors = Supervisor::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json($vehicles);
        }

        return view('vehicles.index', compact('vehicles', 'stages', 'supervisors'));
    }

    public function create()
    {
        $stages = Qs::getStages();
        $supervisors = Supervisor::orderBy('name')->get();

        return view('vehicles.create', compact('stages', 'supervisors'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->canEdit('vehicles')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'plate' => 'required|string|unique:vehicles,plate|max:255',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'nullable|string|max:4',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'stage' => 'required|string|in:'.implode(',', Qs::getStages()),
            'assigned_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'checklist_done' => 'nullable|integer|min:0',
            'checklist_total' => 'nullable|integer|min:1',
            'labor_cost' => 'nullable|numeric|min:0',
            'invoice_total' => 'nullable|numeric|min:0',
        ]);

        $validated['intake_date'] = Carbon::now();
        $validated['labor_cost'] = $validated['labor_cost'] ?? 0.00;
        $validated['invoice_total'] = $validated['invoice_total'] ?? 0.00;
        $validated['checklist_done'] = $validated['checklist_done'] ?? 0;
        $validated['checklist_total'] = $validated['checklist_total'] ?? 3;

        if ($validated['stage'] === '8. Completed & Dispatched') {
            $validated['completed_at'] = Carbon::now();
        }

        $vehicle = Vehicle::create($validated);

        // Record initial stage history entry
        VehicleStageHistory::create([
            'vehicle_id' => $vehicle->id,
            'stage' => $vehicle->stage,
            'transitioned_at' => Carbon::now(),
        ]);

        ActivityLog::record(Auth::user()->name, "Job card created for {$vehicle->plate} ({$vehicle->make} {$vehicle->model}).");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'vehicle' => $vehicle], 201);
        }

        return redirect()->route('vehicles.show', $vehicle->id)->with('flash_success', "Job Card #{$vehicle->plate} registered successfully.");
    }

    public function show($id)
    {
        $vehicle = Vehicle::with(['stageHistories', 'parts.material'])->findOrFail($id);
        $stages = Qs::getStages();
        $materials = Material::where('qty', '>', 0)->orderBy('name')->get();
        $supervisors = Supervisor::orderBy('name')->get();

        return view('vehicles.show', compact('vehicle', 'stages', 'materials', 'supervisors'));
    }

    public function edit($id)
    {
        if (! Auth::user()->canEdit('vehicles')) {
            abort(403, 'Unauthorized action.');
        }

        $vehicle = Vehicle::findOrFail($id);
        $stages = Qs::getStages();
        $supervisors = Supervisor::orderBy('name')->get();

        return view('vehicles.edit', compact('vehicle', 'stages', 'supervisors'));
    }

    public function update(Request $request, $id)
    {
        if (! Auth::user()->canEdit('vehicles')) {
            abort(403, 'Unauthorized action.');
        }

        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'plate' => 'required|string|max:255|unique:vehicles,plate,'.$vehicle->id,
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'nullable|string|max:4',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'stage' => 'required|string|in:'.implode(',', Qs::getStages()),
            'assigned_to' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'checklist_done' => 'nullable|integer|min:0',
            'checklist_total' => 'nullable|integer|min:1',
            'labor_cost' => 'nullable|numeric|min:0',
            'invoice_total' => 'nullable|numeric|min:0',
        ]);

        $stageChanged = ($vehicle->stage !== $validated['stage']);
        $oldStage = $vehicle->stage;

        if ($stageChanged && $validated['stage'] === '8. Completed & Dispatched' && ! $vehicle->completed_at) {
            $validated['completed_at'] = Carbon::now();
        }

        $vehicle->update($validated);

        if ($stageChanged) {
            VehicleStageHistory::create([
                'vehicle_id' => $vehicle->id,
                'stage' => $validated['stage'],
                'transitioned_at' => Carbon::now(),
            ]);

            ActivityLog::record(Auth::user()->name, "{$vehicle->plate} moved from '{$oldStage}' to '{$validated['stage']}'.");
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'vehicle' => $vehicle]);
        }

        return redirect()->route('vehicles.show', $vehicle->id)->with('flash_success', 'Job Card updated successfully.');
    }

    public function updateStage(Request $request, $id)
    {
        if (! Auth::user()->canEdit('vehicles')) {
            abort(403, 'Unauthorized action.');
        }

        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'stage' => 'required|string|in:'.implode(',', Qs::getStages()),
            'assigned_to' => 'nullable|string|max:255',
        ]);

        $oldStage = $vehicle->stage;
        $newStage = $validated['stage'];
        $updateData = [];

        if (array_key_exists('assigned_to', $validated)) {
            $updateData['assigned_to'] = $validated['assigned_to'];
        }

        if ($oldStage !== $newStage) {
            $updateData['stage'] = $newStage;
            if ($newStage === '8. Completed & Dispatched' && ! $vehicle->completed_at) {
                $updateData['completed_at'] = Carbon::now();
            }

            // Reset checklist progress for next stage
            $updateData['checklist_done'] = 0;
            $updateData['checklist_total'] = 3;

            $vehicle->update($updateData);

            VehicleStageHistory::create([
                'vehicle_id' => $vehicle->id,
                'stage' => $newStage,
                'transitioned_at' => Carbon::now(),
            ]);

            ActivityLog::record(Auth::user()->name, "{$vehicle->plate} transitioned to {$newStage}".(! empty($validated['assigned_to']) ? " (Lead: {$validated['assigned_to']})" : '').'.');
        } elseif (! empty($updateData)) {
            $vehicle->update($updateData);
            ActivityLog::record(Auth::user()->name, "{$vehicle->plate} stage lead updated to '{$validated['assigned_to']}'.");
        }

        return back()->with('flash_success', 'Stage and supervisor assignments updated successfully.');
    }

    public function updateChecklist(Request $request, $id)
    {
        if (! Auth::user()->canEdit('vehicles')) {
            abort(403, 'Unauthorized action.');
        }

        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'checklist_done' => 'required|integer|min:0',
            'checklist_total' => 'required|integer|min:1',
        ]);

        $vehicle->update([
            'checklist_done' => min($validated['checklist_done'], $validated['checklist_total']),
            'checklist_total' => $validated['checklist_total'],
        ]);

        return back()->with('flash_success', 'Stage checklist progress updated.');
    }

    /**
     * Module 3.3 / Section 6.1: Atomic Parts Issuance Transaction
     */
    public function issuePart(Request $request, $id)
    {
        if (! Auth::user()->canEdit('vehicles') && ! Auth::user()->canEdit('materials') && ! Auth::user()->canEdit('vehicle_parts')) {
            abort(403, 'Unauthorized action.');
        }

        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'qty' => 'required|numeric|min:0.01',
            'person' => 'nullable|string|max:255',
        ]);

        $requestedQty = (float) $validated['qty'];
        $personTakingPart = $validated['person'] ?? ($vehicle->assigned_to ?: Auth::user()->name);

        try {
            DB::transaction(function () use ($vehicle, $validated, $requestedQty, $personTakingPart) {
                // Lock material record for update to guarantee atomicity and prevent race conditions
                $material = Material::where('id', $validated['material_id'])->lockForUpdate()->firstOrFail();

                if ((float) $material->qty < $requestedQty) {
                    throw new \Exception("Only {$material->qty} {$material->unit} of '{$material->name}' currently available in stock.");
                }

                $cost = round($requestedQty * (float) $material->unit_cost, 2);

                // 1. Decrement material inventory
                $material->decrement('qty', $requestedQty);

                // 2. Insert vehicle_part record
                VehiclePart::create([
                    'vehicle_id' => $vehicle->id,
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'qty' => $requestedQty,
                    'unit_cost' => $material->unit_cost,
                    'cost' => $cost,
                    'issued_at' => Carbon::now(),
                ]);

                // 3. Record material movement (type = 'out')
                MaterialMovement::create([
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'type' => 'out',
                    'qty' => $requestedQty,
                    'unit' => $material->unit,
                    'date' => Carbon::now()->toDateString(),
                    'person' => $personTakingPart,
                    'vehicle_id' => $vehicle->id,
                    'vehicle_label' => "{$vehicle->plate} — {$vehicle->make} {$vehicle->model}",
                    'note' => "Issued by store to car #{$vehicle->plate}.",
                ]);

                // 4. Record audit log
                ActivityLog::record(Auth::user()->name, "Issued {$requestedQty} {$material->unit} of {$material->name} to {$vehicle->plate} (Taken by: {$personTakingPart}).");
            });

            return back()->with('flash_success', 'Part issued to Job Card and deducted from store inventory.');
        } catch (\Exception $e) {
            return back()->with('flash_danger', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        if (! Auth::user()->canDelete()) {
            abort(403, 'Only administrators can delete vehicle job cards.');
        }

        $vehicle = Vehicle::findOrFail($id);
        $plate = $vehicle->plate;
        $vehicle->delete();

        ActivityLog::record(Auth::user()->name, "Deleted Job Card for vehicle {$plate}.");

        return redirect()->route('vehicles.index')->with('flash_success', "Vehicle {$plate} deleted.");
    }

    public function printJobCard($id)
    {
        $vehicle = Vehicle::with(['stageHistories', 'parts.material'])->findOrFail($id);

        return view('print.job_card', compact('vehicle'));
    }
}
