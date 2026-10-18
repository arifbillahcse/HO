<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Addresses visitors or search engines asked for that do not exist (Admin → SEO → 404 Monitor).
 */
class NotFoundLog extends Model
{
    /** Oldest entries are removed beyond this, so bots probing the site cannot fill the database. */
    public const MAX_ROWS = 2000;

    protected $fillable = ['path', 'hits', 'referrer', 'user_agent', 'last_hit_at'];

    protected $casts = ['last_hit_at' => 'datetime'];

    public static function record(string $path, ?string $referrer, ?string $userAgent): void
    {
        $path = mb_substr('/' . ltrim(rawurldecode($path), '/'), 0, 255);
        $values = [
            'referrer' => $referrer ? mb_substr($referrer, 0, 500) : null,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 500) : null,
            'last_hit_at' => now(),
        ];

        if (self::query()->where('path', $path)->increment('hits', 1, array_filter($values)) > 0) {
            return;
        }

        self::query()->create(['path' => $path, 'hits' => 1, ...$values]);

        $overflow = self::query()->count() - self::MAX_ROWS;
        if ($overflow > 0) {
            self::query()->orderBy('last_hit_at')->limit($overflow)->get(['id'])->each->delete();
        }
    }
}
