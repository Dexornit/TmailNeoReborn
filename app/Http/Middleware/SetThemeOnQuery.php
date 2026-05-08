<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetThemeOnQuery {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        try {
            // Get theme from query parameter first, then from session
            $theme = $request->query('theme') ?: session('theme');

            // If we have a theme and it exists, store it in session and config
            if ($theme && is_dir(resource_path('views/frontend/themes/' . $theme))) {
                session(['theme' => $theme]);
                config(['app.settings.theme' => $theme]);
            }
        } catch (Exception  $e) {
            Log::alert($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        return $next($request);
    }
}
