<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if ($user && $user->isLocked()) {
            return back()->withErrors([
                'username' => 'Account is locked. Try again after ' . $user->locked_until->diffForHumans(),
            ])->withInput($request->only('username'));
        }

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'username' => 'Your account has been deactivated.',
            ])->withInput($request->only('username'));
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password], $request->boolean('remember'))
            || Auth::attempt(['email' => $request->username, 'password' => $request->password], $request->boolean('remember'))) {

            $request->session()->regenerate();
            $authUser = Auth::user();
            $authUser->resetFailedAttempts();
            $authUser->update(['last_login_at' => now()]);

            AuditLog::record('login', 'users', $authUser->id);

            if ($authUser->force_password_change) {
                return redirect()->route('password.force-change');
            }

            return redirect()->intended(route('dashboard'));
        }

        if ($user) {
            $user->incrementFailedAttempts();
            AuditLog::record('failed_login', 'users', $user->id);
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::record('logout', 'users', Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
