<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\PasswordComplexity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeController extends Controller
{
    public function show()
    {
        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', new PasswordComplexity],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Password changed successfully.');
    }
}
