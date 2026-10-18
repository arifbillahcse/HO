<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public const PAGES = [
        'home' => 'Home page',
        'web_hosting' => 'Web Hosting',
        'turbo_hosting' => 'Turbo Hosting',
        'reseller_hosting' => 'Reseller Hosting',
        'student_hosting' => 'Student Hosting',
        'vps' => 'VPS',
        'business_email' => 'Business Email',
        'offer' => 'Offer',
        'package' => 'Package',
    ];

    public const DEFAULT_ICON = 'assets/images/pricing/data-server.svg';

    protected $fillable = [
        'page_key', 'sort_order', 'is_visible', 'name', 'description', 'is_popular', 'badge_text',
        'icon', 'icon_alt', 'features', 'show_original_price', 'button_text',
        'whmcs_pid', 'yearly_price', 'order_url', 'comparison',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'is_popular' => 'boolean',
            'show_original_price' => 'boolean',
            'features' => 'array',
            'comparison' => 'array',
            'whmcs_pid' => 'integer',
            'yearly_price' => 'decimal:2',
        ];
    }

    public function scopeOnPage(Builder $query, string $page): Builder
    {
        return $query->where('page_key', $page)->orderBy('sort_order')->orderBy('id');
    }

    public function pageLabel(): string
    {
        return self::PAGES[$this->page_key] ?? $this->page_key;
    }
}
