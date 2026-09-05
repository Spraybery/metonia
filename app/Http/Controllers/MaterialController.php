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
        $outwardMovements = MaterialMovement::where('type', 'out')->with('vehicle')->orderByDesc('date')->orderByDesc('id')->get();

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
            'outwardMovements',
            'totalStockValue',
            'lowStockCount'
        ));
    }

    public function issuance(Request $request)
    {
        $materials = Material::orderBy('name')->get();
        $activeVehicles = Vehicle::where('stage', '!=', '8. Completed & Dispatched')->orderBy('plate')->get();

        $query = MaterialMovement::where('type', 'out')->with(['material', 'vehicle']);

        if ($request->filled('search')) {
            $s = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($s) {
                $q->where('material_name', 'like', $s)
                    ->orWhere('vehicle_label', 'like', $s)
                    ->orWhere('issued_by', 'like', $s)
                    ->orWhere('issued_to', 'like', $s);
            });
        }

        $outwardMovements = $query->orderByDesc('date')->orderByDesc('id')->get();

        return view('materials.issuance', compact('materials', 'activeVehicles', 'outwardMovements'));
    }

    public function restock(Request $request)
    {
        $materials = Material::orderBy('name')->get();

        $query = MaterialMovement::where('type', 'in')->with('material');

        if ($request->filled('search')) {
            $s = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($s) {
                $q->where('material_name', 'like', $s)
                    ->orWhere('note', 'like', $s)
                    ->orWhere('person', 'like', $s);
            });
        }

        $restockMovements = $query->orderByDesc('date')->orderByDesc('id')->get();

        return view('materials.restock', compact('materials', 'restockMovements'));
    }

    public function safetyStock(Request $request)
    {
        $categories = Qs::getMaterialCategories();

        $query = Material::where(function ($q) {
            $q->where('category', 'Worker Safety & PPE')
                ->orWhere('category', 'Reflecting & Safety')
                ->orWhere('name', 'like', '%Safety%')
                ->orWhere('name', 'like', '%PPE%')
                ->orWhere('name', 'like', '%Helmet%')
                ->orWhere('name', 'like', '%Glove%')
                ->orWhere('name', 'like', '%Boot%')
                ->orWhere('name', 'like', '%Goggle%')
                ->orWhere('name', 'like', '%Mask%')
                ->orWhere('name', 'like', '%First Aid%');
        })->orderBy('name');

        if ($request->filled('search')) {
            $s = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('supplier', 'like', $s);
            });
        }

        $materials = $query->get();

        $totalSafetyItems = $materials->count();
        $totalUnitsOnHand = (float) $materials->sum('qty');
        $lowStockSafetyItems = $materials->filter(fn (Material $m) => $m->isLowStock())->count();
        $outOfStockCount = $materials->filter(fn (Material $m) => (float) $m->qty <= 0)->count();

        $allCatalogMaterials = Material::orderBy('name')->get();

        return view('materials.safety_stock', compact(
            'materials',
            'categories',
            'allCatalogMaterials',
            'totalSafetyItems',
            'totalUnitsOnHand',
            'lowStockSafetyItems',
            'outOfStockCount'
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
                'issued_by' => Auth::user()->name,
                'issued_to' => Auth::user()->name,
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
            'person' => 'nullable|string|max:255',
            'issued_by' => 'nullable|string|max:255',
            'issued_to' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'note' => 'nullable|string',
        ]);

        $qty = (float) $validated['qty'];
        $issuedBy = $validated['issued_by'] ?? Auth::user()->name;
        $issuedTo = $validated['issued_to'] ?? ($validated['person'] ?? Auth::user()->name);

        if ($validated['type'] === 'out' && (float) $material->qty < $qty) {
            return back()->with('flash_danger', "Insufficient stock: only {$material->qty} {$material->unit} available.")->withInput();
        }

        DB::transaction(function () use ($material, $validated, $qty, $issuedBy, $issuedTo) {
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
                        'issued_by' => $issuedBy,
                        'issued_to' => $issuedTo,
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
                'person' => $issuedTo,
                'issued_by' => $issuedBy,
                'issued_to' => $issuedTo,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'vehicle_label' => $vehicleLabel,
                'note' => $note,
            ]);

            $action = $validated['type'] === 'in' ? 'Restocked from supplier' : 'Issued/Dispatched';
            ActivityLog::record(Auth::user()->name, "{$action} {$qty} {$material->unit} of '{$material->name}' (Issued By: {$issuedBy}, Issued To: {$issuedTo}".($vehicleLabel ? " for {$vehicleLabel}" : '').').');
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

    public function updateMovement(Request $request, $id)
    {
        if (! Auth::user()->canEdit('materials')) {
            abort(403, 'Unauthorized action.');
        }

        $movement = MaterialMovement::findOrFail($id);

        $validated = $request->validate([
            'qty' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'issued_by' => 'nullable|string|max:255',
            'issued_to' => 'nullable|string|max:255',
            'person' => 'nullable|string|max:255',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'note' => 'nullable|string',
        ]);

        $oldQty = (float) $movement->qty;
        $newQty = (float) $validated['qty'];
        $qtyDiff = $newQty - $oldQty;

        try {
            DB::transaction(function () use ($movement, $validated, $newQty, $qtyDiff) {
                $material = Material::where('id', $movement->material_id)->lockForUpdate()->first();

                if ($movement->type === 'out') {
                    if ($material && $qtyDiff > 0 && (float) $material->qty < $qtyDiff) {
                        throw new \Exception("Insufficient inventory stock: only {$material->qty} {$material->unit} available to cover adjustment.");
                    }

                    if ($material) {
                        $material->decrement('qty', $qtyDiff);
                    }

                    $targetVehicleId = $validated['vehicle_id'] ?? $movement->vehicle_id;
                    if ($targetVehicleId) {
                        $part = VehiclePart::where('vehicle_id', $targetVehicleId)
                            ->where('material_id', $movement->material_id)
                            ->latest('id')
                            ->first();

                        if ($part) {
                            $unitCost = (float) ($material ? $material->unit_cost : $part->unit_cost);
                            $part->update([
                                'qty' => $newQty,
                                'cost' => round($newQty * $unitCost, 2),
                                'issued_by' => $validated['issued_by'] ?? $movement->issued_by,
                                'issued_to' => $validated['issued_to'] ?? $movement->issued_to,
                            ]);
                        }
                    }
                } elseif ($movement->type === 'in') {
                    if ($material) {
                        $material->increment('qty', $qtyDiff);
                    }
                }

                $vehicleLabel = null;
                $targetVehicleId = $validated['vehicle_id'] ?? $movement->vehicle_id;
                if (! empty($targetVehicleId)) {
                    $v = Vehicle::find($targetVehicleId);
                    if ($v) {
                        $vehicleLabel = "{$v->plate} — {$v->make} {$v->model}";
                    }
                }

                $issuedBy = $validated['issued_by'] ?? ($movement->issued_by ?: Auth::user()->name);
                $issuedTo = $validated['issued_to'] ?? ($validated['person'] ?? $movement->issued_to);

                $movement->update([
                    'qty' => $newQty,
                    'date' => $validated['date'],
                    'person' => $issuedTo,
                    'issued_by' => $issuedBy,
                    'issued_to' => $issuedTo,
                    'vehicle_id' => $targetVehicleId,
                    'vehicle_label' => $vehicleLabel ?: $movement->vehicle_label,
                    'note' => $validated['note'] ?? $movement->note,
                ]);

                ActivityLog::record(Auth::user()->name, "Corrected material issuance record #{$movement->id} for '{$movement->material_name}' (New Qty: {$newQty}).");
            });
        } catch (\Exception $e) {
            return back()->with('flash_danger', $e->getMessage())->withInput();
        }

        return back()->with('flash_success', 'Material issuance record updated successfully.');
    }

    public function destroyMovement($id)
    {
        if (! Auth::user()->canEdit('materials')) {
            abort(403, 'Unauthorized action.');
        }

        $movement = MaterialMovement::findOrFail($id);

        DB::transaction(function () use ($movement) {
            $material = Material::find($movement->material_id);

            if ($movement->type === 'out') {
                if ($material) {
                    $material->increment('qty', (float) $movement->qty);
                }

                if ($movement->vehicle_id) {
                    VehiclePart::where('vehicle_id', $movement->vehicle_id)
                        ->where('material_id', $movement->material_id)
                        ->latest('id')
                        ->first()?->delete();
                }
            } elseif ($movement->type === 'in') {
                if ($material) {
                    $material->decrement('qty', (float) $movement->qty);
                }
            }

            $name = $movement->material_name;
            $qty = $movement->qty;
            $movement->delete();

            ActivityLog::record(Auth::user()->name, "Deleted material movement record of {$qty} for '{$name}'. Stock reverted.");
        });

        return back()->with('flash_success', 'Issuance record deleted and store stock reverted.');
    }
}
