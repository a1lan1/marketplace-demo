<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IdempotencyMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'idempotency' => IdempotencyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                // Handle Authentication errors
                if ($e instanceof AuthenticationException) {
                    return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
                }

                // Handle Authorization errors
                if ($e instanceof AuthorizationException) {
                    return response()->json(['message' => $e->getMessage() ?: 'This action is unauthorized.'], Response::HTTP_FORBIDDEN);
                }

                // Handle "model not found" errors
                if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                    return response()->json(['message' => 'The requested resource was not found.'], Response::HTTP_NOT_FOUND);
                }

                // Handle validation errors
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => $e->errors(),
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                // Handle throttling errors
                if ($e instanceof ThrottleRequestsException) {
                    return response()->json(['message' => 'Too many attempts.'], Response::HTTP_TOO_MANY_REQUESTS);
                }

                // Handle other generic HTTP errors (like 405 Method Not Allowed)
                if ($e instanceof HttpException) {
                    return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
                }

                // All other errors will be 500
                $message = config('app.debug') ? $e->getMessage() : 'A server error occurred.';

                return response()->json(['message' => $message], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    })->create();
