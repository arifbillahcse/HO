<?php

namespace App\Services;

/**
 * Business details for search engines (Admin → SEO → Local SEO), turned into the
 * Organization and LocalBusiness structured data on the pages. Name, phone,
 * email, address, map location and social links come from Contact & Address.
 */
class LocalSeo
{
    public const TYPES = [
        'LocalBusiness' => 'Local business (has an office, shows opening hours)',
        'ProfessionalService' => 'Professional service (has an office, shows opening hours)',
        'Store' => 'Store (has an office, shows opening hours)',
        'OnlineBusiness' => 'Online business (no office or opening hours)',
        'Organization' => 'Organization (no office or opening hours)',
    ];

    /** Types that are a LocalBusiness and may have opening hours and a price range. */
    private const LOCAL_TYPES = ['LocalBusiness', 'ProfessionalService', 'Store'];

    public const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public const DEFAULT_DESCRIPTION = 'Hostorio is a web hosting services provider in Bangladesh offering shared hosting, turbo hosting, reseller hosting, VPS, domain registration, and business email solutions.';

    /** Setting keys managed by the Local SEO page. */
    public const KEYS = [
        'biz_type', 'biz_description', 'biz_logo', 'biz_price_range', 'biz_area_served', 'biz_languages',
        'biz_hours_247', 'biz_hours',
    ];

    public function __construct(private BusinessInfo $business)
    {
    }

    public function type(): string
    {
        $type = (string) SettingsService::get('biz_type', 'LocalBusiness');

        return array_key_exists($type, self::TYPES) ? $type : 'LocalBusiness';
    }

    public function isLocal(): bool
    {
        return in_array($this->type(), self::LOCAL_TYPES, true);
    }

    public function description(): string
    {
        return trim((string) SettingsService::get('biz_description', '')) ?: self::DEFAULT_DESCRIPTION;
    }

    public function logoUrl(): string
    {
        $path = trim((string) SettingsService::get('biz_logo', ''));

        return $path !== '' ? asset('storage/' . ltrim($path, '/')) : asset('assets/images/logo/hostorio-logo.png');
    }

    public function priceRange(): string
    {
        return trim((string) SettingsService::get('biz_price_range', '৳৳'));
    }

    public function areaServed(): string
    {
        return trim((string) SettingsService::get('biz_area_served', 'BD'));
    }

    /** @return list<string> */
    public function languages(): array
    {
        $languages = SettingsService::get('biz_languages', ['English', 'Bengali']);

        return array_values(array_filter(array_map('trim', is_array($languages) ? $languages : [])));
    }

    public function isOpen247(): bool
    {
        return filter_var(SettingsService::get('biz_hours_247', true), FILTER_VALIDATE_BOOLEAN);
    }

    /** @return list<array{days: list<string>, opens: string, closes: string}> */
    public function hours(): array
    {
        if ($this->isOpen247()) {
            return [['days' => self::DAYS, 'opens' => '00:00', 'closes' => '23:59']];
        }

        $rows = SettingsService::get('biz_hours', []);

        return collect(is_array($rows) ? $rows : [])
            ->map(fn ($row) => [
                'days' => array_values(array_intersect(self::DAYS, (array) ($row['days'] ?? []))),
                'opens' => substr((string) ($row['opens'] ?? ''), 0, 5),
                'closes' => substr((string) ($row['closes'] ?? ''), 0, 5),
            ])
            ->filter(fn (array $row) => $row['days'] !== [] && preg_match('/^\d\d:\d\d$/', $row['opens']) && preg_match('/^\d\d:\d\d$/', $row['closes']))
            ->values()
            ->all();
    }

    /* ───────────────────────── structured data ───────────────────────── */

    public function organization(bool $withContext = true): array
    {
        return array_filter([
            '@context' => $withContext ? 'https://schema.org' : null,
            '@type' => $this->type() === 'OnlineBusiness' ? 'OnlineBusiness' : 'Organization',
            '@id' => Seo::url() . '#organization',
            'name' => $this->business->name(),
            'url' => Seo::url(),
            'logo' => $this->logoUrl(),
            'description' => $this->description(),
            'contactPoint' => array_filter([
                '@type' => 'ContactPoint',
                'telephone' => $this->business->phone(),
                'email' => $this->business->email(),
                'contactType' => 'customer service',
                'areaServed' => $this->areaServed(),
                'availableLanguage' => $this->languages(),
            ]),
            'address' => $this->business->street() !== '' ? $this->business->postalAddress() : null,
            'sameAs' => $this->business->sameAs(),
        ]);
    }

    public function localBusiness(): ?array
    {
        if (! $this->isLocal()) {
            return null;
        }

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => $this->type(),
            '@id' => Seo::url() . '#localbusiness',
            'name' => $this->business->name(),
            'image' => $this->logoUrl(),
            'url' => Seo::url(),
            'telephone' => $this->business->phone(),
            'email' => $this->business->email(),
            'address' => $this->business->postalAddress(),
            'geo' => $this->business->geo(),
            'openingHoursSpecification' => array_map(fn (array $row) => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $row['days'],
                'opens' => $row['opens'],
                'closes' => $row['closes'],
            ], $this->hours()),
            'priceRange' => $this->priceRange(),
            'areaServed' => $this->areaServed(),
        ]);
    }

    /** JSON safe to print inside a <script> tag. */
    public static function json(array $data): string
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
