<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
            'remember' => 'nullable',
        ]);

        $field = filter_var($validated['identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $validated['identifier'],
            'password' => $validated['password'],
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            ActivityLog::record($user->name, "{$user->name} ({$user->role}) signed into the system.");

            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => true,
                    'user' => $user,
                ]);
            }

            return redirect()->intended(route('dashboard'))->with('flash_success', "Welcome back, {$user->name}!");
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => false,
                'msg' => 'Invalid username/email or password.',
            ], 401);
        }

        return back()->withErrors([
            'identifier' => 'The provided credentials do not match our plant records.',
        ])->onlyInput('identifier');
    }

    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            ActivityLog::record($user->name, "{$user->name} signed out.");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('flash_success', 'You have been safely signed out.');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::record($user->name, "{$user->name} updated account password.");

        return back()->with('flash_success', 'Password updated successfully.');
    }
}
