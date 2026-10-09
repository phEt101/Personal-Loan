<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $isActive = $user->newQuery()
            ->whereKey($user->getAuthIdentifier())
            ->value('is_active');

        if ($isActive) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = __('auth::messages.account_disabled');

        if ($request->ajax() || $request->expectsJson()) {
            $response = response()->json([
                'message' => $message,
                'login_url' => route('login'),
            ], 401);
        } else {
            $response = redirect()->route('login')->with('status', $message);
        }

        return $response;
    }
}
