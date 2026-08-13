<?php

use App\Exceptions\Workflow\UnauthorizedApprovalException;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\EnsureCorrelationId;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\ScopePlantMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo('/login');
        $middleware->append(EnsureCorrelationId::class);
        $middleware->api(append: [
            CorsMiddleware::class,
        ]);
        $middleware->web(append: [
            EnsureUserIsActive::class,
            ScopePlantMiddleware::class,
            CheckMaintenanceMode::class,
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                if ($e instanceof UnauthorizedException
                    || $e instanceof AuthorizationException
                    || $e instanceof AccessDeniedHttpException
                    || $e instanceof UnauthorizedApprovalException) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'Anda tidak memiliki hak akses untuk melakukan tindakan ini.',
                        'data' => null,
                        'errors' => null,
                        'timestamp' => now()->toIso8601String(),
                    ], 403);
                }

                if ($e instanceof NotFoundHttpException
                    || $e instanceof ModelNotFoundException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resource atau endpoint tidak ditemukan.',
                        'data' => null,
                        'errors' => null,
                        'timestamp' => now()->toIso8601String(),
                    ], 404);
                }
            }
        });
    })->create();
