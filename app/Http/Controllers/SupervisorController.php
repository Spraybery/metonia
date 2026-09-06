<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\ActivityLog;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
{
    public function index(Request $request)
    {
        $supervisors = Supervisor::orderBy('name')->get();
        $stages = array_merge(['All Stages'], Qs::getStages());

        if ($request->wantsJson()) {
            return response()->json($supervisors);
        }

        return view('supervisors.index', compact('supervisors', 'stages'));
    }

    public function printRegister()
    {
        $supervisors = Supervisor::orderBy('name')->get();

        return view('print.supervisors_roster', compact('supervisors'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->canEdit('supervisors')) {
            abort(403, 'Unauthorized action.');
        }

        $stages = array_merge(['All Stages'], Qs::getStages());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stage' => 'required|string|in:'.implode(',', $stages),
            'phone' => 'nullable|string|max:255',
        ]);

        $supervisor = Supervisor::create($validated);
        ActivityLog::record(Auth::user()->name, "Added supervisor '{$supervisor->name}'.");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'supervisor' => $supervisor], 201);
        }

        return back()->with('flash_success', "Supervisor '{$supervisor->name}' added to roster.");
    }

    public function update(Request $request, $id)
    {
        if (! Auth::user()->canEdit('supervisors')) {
            abort(403, 'Unauthorized action.');
        }

        $supervisor = Supervisor::findOrFail($id);
        $stages = array_merge(['All Stages'], Qs::getStages());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stage' => 'required|string|in:'.implode(',', $stages),
            'phone' => 'nullable|string|max:255',
        ]);

        $supervisor->update($validated);
        ActivityLog::record(Auth::user()->name, "Updated roster details for supervisor '{$supervisor->name}'.");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'supervisor' => $supervisor]);
        }

        return back()->with('flash_success', "Supervisor '{$supervisor->name}' updated.");
    }

    public function destroy($id)
    {
        if (! Auth::user()->canDelete()) {
            abort(403, 'Only administrators can remove supervisors.');
        }

        $supervisor = Supervisor::findOrFail($id);
        $name = $supervisor->name;
        $supervisor->delete();

        ActivityLog::record(Auth::user()->name, "Removed supervisor '{$name}' from roster.");

        return back()->with('flash_success', "Supervisor '{$name}' removed.");
    }
}
