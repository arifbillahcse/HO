<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Search engine settings for one website page, edited under Admin → SEO → Page SEO.
 */
class SeoPage extends Model
{
    /** page_key => [address, page name], in menu order. */
    public const PAGES = [
        'home' => ['', 'Home'],
        'web_hosting' => ['web-hosting', 'Web Hosting'],
        'turbo_hosting' => ['turbo-hosting', 'Turbo Hosting'],
        'reseller_hosting' => ['reseller-hosting', 'Reseller Hosting'],
        'student_hosting' => ['student-hosting', 'Student Hosting'],
        'vps' => ['vps', 'VPS'],
        'business_email' => ['business-email', 'Business Email'],
        'offer' => ['offer', 'Offer'],
        'domain_registration' => ['domain-registration', 'Domain Registration'],
        'about_us' => ['about-us', 'About Us'],
        'privacy_policy' => ['privacy-policy', 'Privacy Policy'],
        'terms_and_conditions' => ['terms-and-conditions', 'Terms and Conditions'],
    ];

    protected $fillable = [
        'page_key', 'sort_order', 'name', 'seo_title', 'meta_description', 'focus_keyword',
        'canonical_url', 'robots_index', 'robots_follow', 'og_title', 'og_description', 'og_image',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
    ];

    protected $attributes = [
        'robots_index' => true,
        'robots_follow' => true,
    ];

    /** Address of the page without slashes, e.g. "web-hosting" ("" for the homepage). */
    public function slug(): string
    {
        return self::PAGES[$this->page_key][0] ?? str_replace('_', '-', (string) $this->page_key);
    }

    public static function keyForSlug(string $slug): string
    {
        return $slug === '' ? 'home' : str_replace('-', '_', trim($slug, '/'));
    }
}
