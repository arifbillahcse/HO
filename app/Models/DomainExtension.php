<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * How one domain extension shows on the Domain Registration page (Admin → Domains → Domain prices).
 * Prices come live from WHMCS; this only holds order, visibility and badge.
 */
class DomainExtension extends Model
{
    /** Badge choices; null means "use the WHMCS group" (HOT / NEW / SALE set in WHMCS). */
    public const BADGES = ['none' => 'No badge', 'hot' => 'HOT', 'new' => 'NEW', 'sale' => 'SALE'];

    protected $fillable = ['tld', 'sort_order', 'is_visible', 'badge'];

    protected $casts = ['is_visible' => 'boolean'];

    protected $attributes = ['is_visible' => true];
}
