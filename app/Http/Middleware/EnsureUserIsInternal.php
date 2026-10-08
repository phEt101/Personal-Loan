<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsInternal
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->user_type !== 'internal') {
            return redirect()
                ->route('customer-history.index')
                ->with('status', __('messages.authorization.internal_only'));
        }

        return $next($request);
    }
}
