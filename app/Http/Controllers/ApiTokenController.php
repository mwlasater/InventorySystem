<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Self-service management of a user's personal access tokens. The plaintext
 * token is shown exactly once, right after creation; only the hashed value is
 * ever stored, so it can't be retrieved again.
 */
class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        return view('profile.api-tokens', [
            'tokens' => $request->user()->tokens()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Inherit the user's access ('*'); the token is bound to this user, so
        // their role and active status govern what it can do.
        $token = $request->user()->createToken($request->name);

        return redirect()->route('api-tokens.index')
            ->with('token_name', $request->name)
            ->with('plain_text_token', $token->plainTextToken);
    }

    public function destroy(Request $request, string $tokenId)
    {
        // Scoped to the user's own tokens so one user can't revoke another's.
        $request->user()->tokens()->whereKey($tokenId)->delete();

        return redirect()->route('api-tokens.index')
            ->with('success', 'Token revoked.');
    }
}
