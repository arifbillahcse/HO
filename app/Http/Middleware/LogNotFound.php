<?php

namespace App\Http\Middleware;

use App\Models\NotFoundLog;
use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Records addresses that answered "404 not found" (Admin → SEO → 404 Monitor).
 */
class LogNotFound
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 404 && $request->isMethodSafe() && ! Redirect::isIgnored($request->getPathInfo())) {
            try {
                NotFoundLog::record($request->getPathInfo(), $request->headers->get('referer'), $request->userAgent());
            } catch (Throwable $e) {
                Log::warning('404 log failed: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
