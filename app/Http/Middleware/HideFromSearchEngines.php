<?php

namespace App\Http\Middleware;

use App\Services\Seo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * While "Hide site from search engines" is on, tell search engines not to index
 * any response, including the sitemap and images served by Laravel.
 */
class HideFromSearchEngines
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app(Seo::class)->isHidden()) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
