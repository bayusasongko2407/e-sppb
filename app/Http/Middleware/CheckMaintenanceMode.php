<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $maintenanceEnabled = filter_var(AppSetting::get('op_maintenance_mode', false), FILTER_VALIDATE_BOOLEAN);
        } catch (\Throwable $e) {
            $maintenanceEnabled = false;
        }

        if ($maintenanceEnabled) {
            $user = auth()->user();
            $isExempted = $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));

            $path = $request->path();

            // Allow login, logout, and common authentication pathways so admins can access/disable it
            $allowedPaths = [
                'login',
                'logout',
                'admin/login',
                'admin/logout',
            ];

            $isAllowedPath = false;
            foreach ($allowedPaths as $allowed) {
                if ($path === $allowed || str_starts_with($path, $allowed.'/')) {
                    $isAllowedPath = true;
                    break;
                }
            }

            // Allow livewire core routing for dynamic login/updates
            if (str_starts_with($path, 'livewire/')) {
                $isAllowedPath = true;
            }
            if (! $isExempted && ! $isAllowedPath) {
                try {
                    $message = AppSetting::get('op_maintenance_message', 'Aplikasi sedang dalam pemeliharaan berkala. Silakan coba beberapa saat lagi.');
                } catch (\Throwable $e) {
                    $message = 'Aplikasi sedang dalam pemeliharaan berkala. Silakan coba beberapa saat lagi.';
                }

                if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                    return response()->json([
                        'message' => $message,
                    ], 503);
                }

                return response()->view('errors.503', [
                    'message' => $message,
                ], 503);
            }
        }

        return $next($request);
    }
}
