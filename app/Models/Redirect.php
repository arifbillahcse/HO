<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * "Old address → new address", edited under Admin → SEO → Redirects.
 * A source ending in * matches every address that starts with it.
 */
class Redirect extends Model
{
    public const TYPES = [
        301 => '301 Permanent (moved for good)',
        302 => '302 Temporary',
        307 => '307 Temporary (keeps form data)',
        410 => '410 Deleted (gone for good, no new address)',
    ];

    /** Addresses that never redirect and are never logged as 404. */
    public const IGNORED = ['/admin', '/livewire', '/filament', '/storage/', '/up', '/robots.txt', '/sitemap.xml'];

    private const CACHE_KEY = 'seo_redirects';

    protected $fillable = ['source', 'target', 'status_code', 'is_active', 'hits', 'last_hit_at'];

    protected $casts = [
        'status_code' => 'integer',
        'is_active' => 'boolean',
        'last_hit_at' => 'datetime',
    ];

    protected $attributes = [
        'status_code' => 301,
        'is_active' => true,
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** "https://hostorio.com/Old-Page/?a=1" → "/old-page"; "blog/*" → "/blog/*". */
    public static function normalize(string $input): string
    {
        $input = trim($input);
        $wildcard = str_ends_with($input, '*');
        $path = rawurldecode((string) (parse_url(rtrim($input, '*'), PHP_URL_PATH) ?? ''));
        $path = mb_strtolower('/' . ltrim($path, '/'));

        if ($wildcard) {
            return $path . '*';
        }

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public static function isIgnored(string $path): bool
    {
        $path = mb_strtolower($path);

        foreach (self::IGNORED as $prefix) {
            $prefix = rtrim($prefix, '/');

            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * The active redirect for a request path. Exact sources win; otherwise the
     * longest matching "starts with" (*) source. A * in the target is replaced
     * by the rest of the address.
     *
     * @return array{id: int, status: int, target: ?string}|null
     */
    public static function match(string $requestPath): ?array
    {
        $original = '/' . trim(rawurldecode($requestPath), '/');
        $path = mb_strtolower($original);

        $rules = Cache::rememberForever(self::CACHE_KEY, fn () => self::query()
            ->where('is_active', true)
            ->get(['id', 'source', 'target', 'status_code'])
            ->map(fn (self $r) => [$r->id, $r->source, $r->target, $r->status_code])
            ->all());

        $best = null;

        foreach ($rules as [$id, $source, $target, $status]) {
            if (! str_ends_with($source, '*')) {
                if ($source === $path) {
                    return ['id' => $id, 'status' => $status, 'target' => $target];
                }

                continue;
            }

            $prefix = rtrim($source, '*');
            $matches = str_starts_with($path, $prefix) || $path === rtrim($prefix, '/');

            if ($matches && ($best === null || mb_strlen($prefix) > mb_strlen($best['prefix']))) {
                $rest = ltrim(mb_substr($original, mb_strlen($prefix)), '/');
                $best = ['prefix' => $prefix, 'id' => $id, 'status' => $status, 'target' => $target === null ? null : str_replace('*', $rest, $target)];
            }
        }

        return $best ? ['id' => $best['id'], 'status' => $best['status'], 'target' => $best['target']] : null;
    }
}
