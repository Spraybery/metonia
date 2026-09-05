<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\MaterialMovement;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with('movements')->orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $s = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('supplier', 'like', $s);
            });
        }

        $materials = $query->get();
        $categories = Qs::getMaterialCategories();
        $units = Qs::getMaterialUnits();
        $activeVehicles = Vehicle::where('stage', '!=', '8. Completed & Dispatched')->orderBy('plate')->get();

        $totalStockValue = $materials->sum(fn (Material $m) => $m->totalValue());
        $lowStockCount = $materials->filter(fn (Material $m) => $m->isLowStock())->count();

        if ($request->wantsJson()) {
            return response()->json($materials);
        }

        return view('materials.index', compact(
            'materials',
            'categories',
            'units',
            'activeVehicles',
            'totalStockValue',
            'lowStockCount'
        ));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->canEdit('materials')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:'.implode(',', Qs::getMaterialCategories()),
            'unit' => 'required|string|in:'.implode(',', Qs::getMaterialUnits()),
            'qty' => 'required|numeric|min:0',
            'low_stock' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ]);

        $material = Material::create($validated);

        if ((float) $material->qty > 0) {
            MaterialMovement::create([
                'material_id' => $material->id,
                'material_name' => $material->name,
                'type' => 'in',
                'qty' => $material->qty,
                'unit' => $material->unit,
                'date' => Carbon::now()->toDateString(),
                'person' => Auth::user()->name,
                'note' => 'Initial stock on item creation.',
            ]);
        }

        ActivityLog::record(Auth::user()->name, "Created store inventory item '{$material->name}' ({$material->category}).");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'material' => $material], 201);
        }

        return back()->with('flash_success', "Material '{$material->name}' added to inventory.");
    }

    public function update(Request $request, $id)
    {
        if (! Auth::user()->canEdit('materials')) {
            abort(403, 'Unauthorized action.');
        }

        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:'.implode(',', Qs::getMaterialCategories()),
            'unit' => 'required|string|in:'.implode(',', Qs::getMaterialUnits()),
            'low_stock' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ]);

        $material->update($validated);
        ActivityLog::record(Auth::user()->name, "Updated store item specifications for '{$material->name}'.");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'material' => $material]);
        }

        return back()->with('flash_success', "Material '{$material->name}' updated.");
    }

    public function stockMovement(Request $request, $id)
    {
        if (! Auth::user()->canEdit('materials')) {
            abort(403, 'Unauthorized action.');
        }

        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'qty' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'person' => 'required|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'note' => 'nullable|string',
        ]);

        $qty = (float) $validated['qty'];

        if ($validated['type'] === 'out' && (float) $material->qty < $qty) {
            return back()->with('flash_danger', "Insufficient stock: only {$material->qty} {$material->unit} available.")->withInput();
        }

        DB::transaction(function () use ($material, $validated, $qty) {
            $vehicleLabel = null;
            if (! empty($validated['vehicle_id'])) {
                $vehicle = Vehicle::find($validated['vehicle_id']);
                if ($vehicle) {
                    $vehicleLabel = "{$vehicle->plate} — {$vehicle->make} {$vehicle->model}";
                }
            }

            if ($validated['type'] === 'in') {
                $material->increment('qty', $qty);
                if (! empty($validated['supplier'])) {
                    $material->update(['supplier' => $validated['supplier']]);
                }
            } else {
                $material->decrement('qty', $qty);

                // If issued to a vehicle, automatically register into the vehicle's job card parts list
                if (! empty($validated['vehicle_id'])) {
                    $cost = round($qty * (float) $material->unit_cost, 2);
                    VehiclePart::create([
                        'vehicle_id' => $validated['vehicle_id'],
                        'material_id' => $material->id,
                        'material_name' => $material->name,
                        'qty' => $qty,
                        'unit_cost' => $material->unit_cost,
                        'cost' => $cost,
                        'issued_at' => Carbon::now(),
                    ]);
                }
            }

            $note = $validated['note'] ?? null;
            if ($validated['type'] === 'in' && ! empty($validated['supplier'])) {
                $note = trim(($note ? $note.' | ' : '').'Supplier: '.$validated['supplier']);
            }

            MaterialMovement::create([
                'material_id' => $material->id,
                'material_name' => $material->name,
                'type' => $validated['type'],
                'qty' => $qty,
                'unit' => $material->unit,
                'date' => $validated['date'],
                'person' => $validated['person'],
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'vehicle_label' => $vehicleLabel,
                'note' => $note,
            ]);

            $action = $validated['type'] === 'in' ? 'Restocked from supplier' : 'Issued/Dispatched';
            ActivityLog::record(Auth::user()->name, "{$action} {$qty} {$material->unit} of '{$material->name}' (Staff: {$validated['person']}".($vehicleLabel ? " to {$vehicleLabel}" : '').').');
        });

        return back()->with('flash_success', 'Stock movement recorded successfully.');
    }

    public function movements($id)
    {
        $material = Material::with('movements.vehicle')->findOrFail($id);

        return view('materials.movements', compact('material'));
    }

    public function destroy($id)
    {
        if (! Auth::user()->canDelete()) {
            abort(403, 'Only administrators can delete inventory items.');
        }

        $material = Material::findOrFail($id);
        $name = $material->name;
        $material->delete();

        ActivityLog::record(Auth::user()->name, "Deleted store material '{$name}'.");

        return back()->with('flash_success', "Material '{$name}' deleted.");
    }
}
