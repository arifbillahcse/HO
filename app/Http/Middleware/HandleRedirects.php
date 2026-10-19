<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Sends old addresses to new ones (Admin → SEO → Redirects). Runs before
 * routing, so it also works for addresses no route knows, such as old
 * WordPress URLs.
 */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() && ! Redirect::isIgnored($request->getPathInfo())) {
            try {
                $match = Redirect::match($request->getPathInfo());
            } catch (Throwable $e) {
                Log::warning('Redirect lookup failed: ' . $e->getMessage());
                $match = null; // e.g. tables not migrated yet: never take the site down
            }

            if ($match && ($response = $this->respond($request, $match))) {
                return $response;
            }
        }

        return $next($request);
    }

    /** @param array{id: int, status: int, target: ?string} $match */
    private function respond(Request $request, array $match): ?Response
    {
        if ($match['status'] === 410) {
            $this->countHit($match['id']);

            return response()->view('errors.404', [], 410);
        }

        $target = trim((string) $match['target']);

        if ($target === '') {
            return null;
        }

        if (str_starts_with($target, '/')) {
            // Never redirect an address to itself.
            if (! str_contains($target, '?') && Redirect::normalize($target) === Redirect::normalize($request->getPathInfo())) {
                return null;
            }

            $target = rtrim(url('/'), '/') . $target; // url() keeps https when it is forced
        }

        if (! str_contains($target, '?') && ($query = $request->getQueryString())) {
            $target .= '?' . $query;
        }

        $this->countHit($match['id']);

        return redirect()->away($target, $match['status']);
    }

    private function countHit(int $id): void
    {
        try {
            Redirect::query()->whereKey($id)->update([
                'hits' => DB::raw('hits + 1'),
                'last_hit_at' => now(),
            ]);
        } catch (Throwable) {
            // counting is best effort
        }
    }
}
