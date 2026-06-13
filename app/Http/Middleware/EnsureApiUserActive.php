<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API counterpart to CheckAccountActive: a deactivated user's tokens stop
 * working immediately. Returns a JSON 403 rather than redirecting to login,
 * which would be wrong for an API client.
 */
class EnsureApiUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->is_active) {
            return response()->json(['message' => 'Your account has been deactivated.'], 403);
        }

        return $next($request);
    }
}
