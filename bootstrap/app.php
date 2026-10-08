<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsInternal;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'internal' => EnsureUserIsInternal::class,
        ]);
        $middleware->prependToPriorityList(
            AuthenticatesRequests::class,
            SetLocale::class,
        );
        $middleware->prependToPriorityList(
            SubstituteBindings::class,
            EnsureUserIsAdmin::class,
        );
        $middleware->prependToPriorityList(
            SubstituteBindings::class,
            EnsureUserIsInternal::class,
        );
        $middleware->web(append: [
            SetLocale::class,
            EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->ajax() && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => __('auth::messages.session_expired'),
                'login_url' => route('login'),
            ], 401);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => __('auth::messages.session_expired'),
                    'login_url' => route('login'),
                ], 419);
            }

            $loginParameters = [];
            $referer = $request->headers->get('referer');
            if ($referer && parse_url($referer, PHP_URL_HOST) === $request->getHost()) {
                $path = parse_url($referer, PHP_URL_PATH) ?: '/';
                $query = parse_url($referer, PHP_URL_QUERY);
                $loginParameters['redirect'] = $path.($query ? '?'.$query : '');
            }

            return redirect()->route('login', $loginParameters)
                ->with('status', __('auth::messages.session_expired'));
        });
    })->create();
