<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopePlantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set scope plant if necessary for multi-tenant or scoped views
        // Example: session(['current_plant_id' => auth()->user()->plant_id]);

        return $next($request);
    }
}
