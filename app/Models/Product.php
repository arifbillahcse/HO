<?php

namespace App\Models;

use App\Services\WhmcsService;
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
    ];

    public const DEFAULT_ICON = 'assets/images/pricing/data-server.svg';

    /**
     * Fixed card words that keep switching language with the page: the
     * "Save" badge and the price period (translation key, English text).
     */
    public const CARD_UI = [
        'home' => ['save' => ['pricing.save30', 'Save 30%'], 'period' => ['pricing.perYear', '/yr']],
        'web_hosting' => ['save' => ['pricing.save30', 'Save 30%'], 'period' => ['pricing.perYear', '/yr']],
        'turbo_hosting' => ['save' => ['turboPricing.saveBadge', 'Save 30%!'], 'period' => ['turboPricing.perMonth', '/mo']],
        'reseller_hosting' => ['save' => ['resellerPricing.saveBadge', 'Save 30%!'], 'period' => ['resellerPricing.perMonth', '/mo']],
        'student_hosting' => ['save' => ['studentPricing.saveBadge', 'Save 30%!'], 'period' => ['studentPricing.perYear', '/yr']],
        'vps' => ['save' => ['pricing.save30', 'Save 30%'], 'period' => ['pricing.perMonth', '/mo']],
        'business_email' => ['save' => ['emailPricing.saveBadge', 'Save 30%!'], 'period' => ['emailPricing.perMonth', '/mo']],
        'offer' => ['save' => ['pricing.save30', 'Save 30%'], 'period' => ['offerPricing.perYear', '/year']],
    ];

    /**
     * Comparison-table style per page: how the price cell reads and how the
     * highlighted plan is marked ("badge" on the column, "star" in the title).
     */
    public const COMPARISON = [
        'web_hosting' => ['price' => 'monthly', 'popular' => 'none'],
        'turbo_hosting' => ['price' => 'monthly', 'popular' => 'none'],
        'reseller_hosting' => ['price' => 'monthly', 'popular' => 'none'],
        'student_hosting' => ['price' => 'yearly', 'popular' => 'star'],
        'offer' => ['price' => 'offer', 'popular' => 'badge'],
    ];

    protected $fillable = [
        'page_key', 'sort_order', 'is_visible', 'name', 'description', 'is_popular', 'badge_text',
        'icon', 'icon_alt', 'features', 'show_original_price', 'button_text',
        'whmcs_pid', 'yearly_price', 'order_url', 'comparison',
    ];

    protected static function booted(): void
    {
        static::saved(function (Product $product): void {
            if ($product->wasRecentlyCreated || $product->wasChanged('whmcs_pid')) {
                WhmcsService::flushPlans();
            }
        });

        static::deleted(fn () => WhmcsService::flushPlans());
    }

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
