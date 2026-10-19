<?php

namespace App\Services;

/**
 * Contact details and address, edited under Admin → Contact & Address.
 */
class BusinessInfo
{
    /** Setting key => icon class, in footer order. */
    public const SOCIALS = [
        'facebook' => 'fab fa-facebook-f',
        'twitter' => 'fab fa-twitter',
        'youtube' => 'fab fa-youtube',
        'linkedin' => 'fab fa-linkedin-in',
    ];

    /** Setting keys managed by the Contact & Address page. */
    public const KEYS = [
        'business_name', 'business_phone', 'business_email', 'client_area_url',
        'business_address', 'business_city', 'business_region', 'business_latitude', 'business_longitude',
        'social_facebook', 'social_twitter', 'social_youtube', 'social_linkedin', 'reviews_url',
    ];

    private function value(string $key): string
    {
        return trim((string) SettingsService::get($key, ''));
    }

    public function name(): string
    {
        return $this->value('business_name') ?: 'Hostorio';
    }

    public function phone(): string
    {
        return $this->value('business_phone');
    }

    /** Phone number for tel: links ("+880 1779-440297" → "+8801779440297"). */
    public function phoneHref(): string
    {
        return 'tel:' . preg_replace('/(?!^\+)[^\d]/', '', $this->phone());
    }

    public function email(): string
    {
        return $this->value('business_email');
    }

    /** Client area address, always ending in "/". */
    public function clientAreaUrl(): string
    {
        return rtrim($this->value('client_area_url') ?: 'https://my.hostorio.com', '/') . '/';
    }

    public function street(): string
    {
        return $this->value('business_address');
    }

    public function city(): string
    {
        return $this->value('business_city');
    }

    public function region(): string
    {
        return $this->value('business_region');
    }

    /** "Kanchan, Rupganj, Narayanganj" */
    public function cityLine(): string
    {
        return implode(', ', array_filter([$this->city(), $this->region()]));
    }

    /** Where testimonials' "Read more" goes, e.g. the Google reviews of the business. Empty hides the link. */
    public function reviewsUrl(): string
    {
        return $this->value('reviews_url');
    }

    /** @return array<string, string> network => url, empty ones left out */
    public function socials(): array
    {
        return collect(self::SOCIALS)
            ->keys()
            ->mapWithKeys(fn (string $network) => [$network => $this->value("social_{$network}")])
            ->filter()
            ->all();
    }

    /* ───────────────────────── structured data ───────────────────────── */

    /** @return list<string> */
    public function sameAs(): array
    {
        return array_values($this->socials());
    }

    public function postalAddress(): array
    {
        return array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $this->street(),
            'addressLocality' => $this->city(),
            'addressRegion' => $this->region(),
            'addressCountry' => 'BD',
        ]);
    }

    public function geo(): ?array
    {
        $lat = $this->value('business_latitude');
        $lng = $this->value('business_longitude');

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        return ['@type' => 'GeoCoordinates', 'latitude' => (float) $lat, 'longitude' => (float) $lng];
    }
}
