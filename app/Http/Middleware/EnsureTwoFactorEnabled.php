<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When the "enforce 2FA for admins" setting is on, nudge admins who haven't set
 * up 2FA to the setup page. The 2FA management routes and logout are exempt so
 * the user can actually complete enrolment (or leave) without a redirect loop.
 */
class EnsureTwoFactorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user
            && Setting::get('enforce_2fa_for_admins')
            && $user->isAdmin()
            && ! $user->hasTwoFactorEnabled()
            && ! $request->routeIs('two-factor.*', 'logout')
        ) {
            return redirect()->route('two-factor.show')
                ->with('status', 'Two-factor authentication is required for administrators. Please set it up to continue.');
        }

        return $next($request);
    }
}
