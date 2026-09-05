<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\ActivityLog;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ToolController extends Controller
{
    public function index(Request $request)
    {
        $query = Tool::orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $s = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('asset_tag', 'like', $s)
                    ->orWhere('brand', 'like', $s)
                    ->orWhere('location', 'like', $s)
                    ->orWhere('assigned_to', 'like', $s)
                    ->orWhere('issued_by', 'like', $s);
            });
        }

        $tools = $query->get();
        $categories = Qs::getToolCategories();
        $statuses = Qs::getToolStatuses();

        $stats = [
            'total' => $tools->count(),
            'available' => $tools->where('status', 'Available')->count(),
            'checked_out' => $tools->where('status', 'Checked Out')->count(),
            'in_maintenance' => $tools->where('status', 'In Maintenance')->count(),
            'calibration_overdue' => $tools->filter(fn (Tool $t) => $t->isCalibrationOverdue())->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json($tools);
        }

        return view('tools.index', compact('tools', 'categories', 'statuses', 'stats'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->canEdit('tools')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'asset_tag' => 'required|string|unique:tools,asset_tag|max:255',
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:'.implode(',', Qs::getToolCategories()),
            'brand' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|in:'.implode(',', Qs::getToolStatuses()),
            'assigned_to' => 'nullable|string|max:255',
            'issued_by' => 'nullable|string|max:255',
            'next_calibration' => 'nullable|date',
        ]);

        if ($validated['status'] === 'Available' || empty($validated['assigned_to'])) {
            $validated['assigned_to'] = 'In Tool Crib';
        }

        $tool = Tool::create($validated);
        ActivityLog::record(Auth::user()->name, "Registered equipment asset '{$tool->name}' [{$tool->asset_tag}].");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'tool' => $tool], 201);
        }

        return back()->with('flash_success', "Tool asset '{$tool->name}' registered successfully.");
    }

    public function update(Request $request, $id)
    {
        if (! Auth::user()->canEdit('tools')) {
            abort(403, 'Unauthorized action.');
        }

        $tool = Tool::findOrFail($id);

        $validated = $request->validate([
            'asset_tag' => 'required|string|max:255|unique:tools,asset_tag,'.$tool->id,
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:'.implode(',', Qs::getToolCategories()),
            'brand' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|string|in:'.implode(',', Qs::getToolStatuses()),
            'assigned_to' => 'nullable|string|max:255',
            'issued_by' => 'nullable|string|max:255',
            'next_calibration' => 'nullable|date',
        ]);

        if ($validated['status'] === 'Available') {
            $validated['assigned_to'] = 'In Tool Crib';
        }

        $oldStatus = $tool->status;
        $tool->update($validated);

        if ($oldStatus !== $validated['status']) {
            ActivityLog::record(Auth::user()->name, "Equipment [{$tool->asset_tag}] status changed from '{$oldStatus}' to '{$validated['status']}'.");
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'tool' => $tool]);
        }

        return back()->with('flash_success', "Tool asset '{$tool->name}' updated.");
    }

    public function destroy($id)
    {
        if (! Auth::user()->canDelete()) {
            abort(403, 'Only administrators can decommission equipment.');
        }

        $tool = Tool::findOrFail($id);
        $name = $tool->name;
        $tag = $tool->asset_tag;
        $tool->delete();

        ActivityLog::record(Auth::user()->name, "Decommissioned tool [{$tag}] {$name}.");

        return back()->with('flash_success', "Tool [{$tag}] removed.");
    }
}
