<?php

namespace App\Http\Controllers;

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
