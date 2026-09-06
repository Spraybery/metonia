<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can manage system users.');
        }

        $users = User::orderBy('name')->get();
        $roles = Qs::getUserRoles();

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can create users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:'.implode(',', Qs::getUserRoles()),
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['username'] = trim($validated['username']);
        $validated['email'] = trim($validated['email']);
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        ActivityLog::record(Auth::user()->name, "Created user account '{$user->username}' ({$user->role}).");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'user' => $user], 201);
        }

        return back()->with('flash_success', "User account for '{$user->name}' created.");
    }

    public function update(Request $request, $id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can update users.');
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$user->id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|in:'.implode(',', Qs::getUserRoles()),
        ]);

        $validated['name'] = trim($validated['name']);
        $validated['username'] = trim($validated['username']);
        $validated['email'] = trim($validated['email']);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        ActivityLog::record(Auth::user()->name, "Updated user account details for '{$user->username}'.");

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'user' => $user]);
        }

        return back()->with('flash_success', "User account '{$user->name}' updated.");
    }

    public function destroy($id)
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete user accounts.');
        }

        $user = User::findOrFail($id);

        // Section 2.1 & 1.4: Self-deletion guard
        if (Auth::id() === $user->id) {
            return back()->with('flash_danger', 'Security Guard: You cannot delete your own active administrator account.');
        }

        $username = $user->username;
        $name = $user->name;
        $user->delete();

        ActivityLog::record(Auth::user()->name, "Deleted user account '{$username}' ({$name}).");

        return back()->with('flash_success', "User account '{$name}' has been deleted.");
    }
}
