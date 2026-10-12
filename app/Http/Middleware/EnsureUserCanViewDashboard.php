<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanViewDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->user_type !== 'internal' && ! $user?->isAdmin()) {
            return redirect()
                ->route('customer-history.index')
                ->with('status', __('messages.authorization.dashboard_only'));
        }

        return $next($request);
    }
}
