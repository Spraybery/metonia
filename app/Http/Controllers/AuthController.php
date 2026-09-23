<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

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

        $identifier = trim($validated['identifier']);
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        // Perform case-insensitive lookup for both username and email
        $user = User::where(function ($query) use ($identifier, $isEmail) {
            if ($isEmail) {
                $query->whereRaw('LOWER(email) = ?', [strtolower($identifier)]);
            } else {
                $query->whereRaw('LOWER(username) = ?', [strtolower($identifier)]);
            }
        })->first();

        if ($user && Hash::check($validated['password'], $user->password) && $user->isPending()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Your account request is still awaiting administrator approval.',
                ], 403);
            }

            return back()->withErrors([
                'identifier' => 'Your account request is still awaiting administrator approval. Please check back once it has been reviewed.',
            ])->onlyInput('identifier');
        }

        if ($user && Hash::check($validated['password'], $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

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

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$user->id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->update([
            'name' => trim($validated['name']),
            'username' => trim($validated['username']),
            'email' => trim($validated['email']),
        ]);

        ActivityLog::record($user->name, "{$user->name} updated their own account details.");

        return back()->with('flash_success', 'Your account details have been updated.');
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

    public function showSignupForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.signup', ['roles' => Qs::getSelfSignupRoles()]);
    }

    public function signup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'role' => 'required|string|in:'.implode(',', Qs::getSelfSignupRoles()),
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'username' => trim($validated['username']),
            'email' => trim($validated['email']),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status' => 'Pending',
        ]);

        ActivityLog::record($user->name, "{$user->name} requested a new '{$user->role}' account — pending administrator approval.");

        return redirect()->route('login')->with('flash_success', 'Your account request has been submitted. An administrator will review it and activate your account shortly.');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        Password::sendResetLink($request->only('email'));

        // Deliberately the same message whether or not the email is registered,
        // so this form can't be used to confirm which company emails exist.
        return back()->with('flash_success', 'If that email address is registered in our system, a password reset link has been sent.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset_password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->update(['password' => Hash::make($password)]);

                ActivityLog::record($user->name, "{$user->name} reset their password via the forgot-password link.");
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('flash_success', 'Your password has been reset. Please sign in.');
        }

        return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}
