<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Rules\PasswordComplexity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('username')->paginate(25);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $permissions = config('permissions.available');
        return view('admin.users.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'display_name' => 'nullable|string|max:100',
            'role' => 'required|in:admin,user',
            'permissions' => 'nullable|array',
        ]);

        $tempPassword = Str::random(12) . '!A1';

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'display_name' => $request->display_name,
            'password' => Hash::make($tempPassword),
            'role' => $request->role,
            'permissions' => $request->permissions ?? config('permissions.defaults'),
            'force_password_change' => true,
        ]);

        AuditLog::record('create', 'users', $user->id, null, $user->only(['username', 'email', 'role']));

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->username}' created. Temporary password: {$tempPassword}");
    }

    public function edit(User $user)
    {
        $permissions = config('permissions.available');
        return view('admin.users.edit', compact('user', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'display_name' => 'nullable|string|max:100',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        $old = $user->only(['username', 'email', 'role', 'is_active', 'permissions']);

        $user->update([
            'username' => $request->username,
            'email' => $request->email,
            'display_name' => $request->display_name,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $request->permissions ?? [],
        ]);

        AuditLog::record('update', 'users', $user->id, $old, $user->only(['username', 'email', 'role', 'is_active', 'permissions']));

        return redirect()->route('admin.users.index')->with('success', "User '{$user->username}' updated.");
    }

    public function resetPassword(User $user)
    {
        $tempPassword = Str::random(12) . '!A1';

        $user->update([
            'password' => Hash::make($tempPassword),
            'force_password_change' => true,
        ]);

        return back()->with('success', "Password reset for '{$user->username}'. New temporary password: {$tempPassword}");
    }

    public function activityLog(User $user)
    {
        $logs = AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.users.activity', compact('user', 'logs'));
    }
}
