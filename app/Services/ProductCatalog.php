<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCatalog
{
    /** @return Collection<int, Product> */
    public function forPage(string $page): Collection
    {
        return Product::query()->onPage($page)->where('is_visible', true)->get();
    }

    public function price(Product $product): string
    {
        return app(WhmcsService::class)->productPrice($product);
    }

    public function url(Product $product): string
    {
        return app(WhmcsService::class)->productUrl($product);
    }

    public function icon(Product $product): string
    {
        return filled($product->icon) ? Storage::disk('public')->url($product->icon) : asset(Product::DEFAULT_ICON);
    }

    /** @return array{low: float|int, high: float|int, count: int} */
    public function priceRange(string $page): array
    {
        $prices = $this->forPage($page)->map(fn (Product $p) => $this->number($this->price($p)))->filter(fn ($p) => $p > 0);

        return ['low' => $prices->min() ?? 0, 'high' => $prices->max() ?? 0, 'count' => $prices->count()];
    }

    /* ───────────────────────── structured data ───────────────────────── */

    /** One Product per plan card (home and web hosting pages). @return list<string> */
    public function productSchemas(string $page, string $category): array
    {
        return $this->forPage($page)->map(fn (Product $p) => $this->json([
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => "{$p->name} {$category} Plan",
            'description' => $this->description($p),
            'image' => asset('assets/images/hostorio-domain-hosting-register.jpg'),
            'brand' => ['@type' => 'Brand', 'name' => 'Hostorio'],
            'offers' => [
                '@type' => 'Offer',
                'url' => $this->pageUrl($page),
                'priceCurrency' => 'BDT',
                'price' => $this->number($this->price($p)),
                'priceValidUntil' => $this->validUntil(),
                'availability' => 'https://schema.org/InStock',
                'shippingDetails' => [
                    '@type' => 'OfferShippingDetails',
                    'shippingDestination' => ['@type' => 'DefinedRegion', 'addressCountry' => 'BD'],
                    'deliveryTime' => [
                        '@type' => 'ShippingDeliveryTime',
                        'handlingTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY'],
                        'transitTime' => ['@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 0, 'unitCode' => 'DAY'],
                    ],
                    'shippingRate' => ['@type' => 'MonetaryAmount', 'value' => 0, 'currency' => 'BDT'],
                ],
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => 'BD',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                    'merchantReturnDays' => 30,
                    'returnMethod' => 'https://schema.org/ReturnByMail',
                    'returnFees' => 'https://schema.org/FreeReturn',
                ],
            ],
        ]))->values()->all();
    }

    /** AggregateOffer and ItemList for the offer page. @return list<string> */
    public function offerSchemas(string $page): array
    {
        $products = $this->forPage($page);
        $range = $this->priceRange($page);

        return [
            $this->json([
                '@context' => 'https://schema.org',
                '@type' => 'AggregateOffer',
                'name' => 'Special Web Hosting Deals',
                'description' => 'Limited time special offers on web hosting packages with free domain, SSL, and premium features at discounted prices.',
                'seller' => ['@type' => 'Organization', 'name' => 'Hostorio'],
                'priceCurrency' => 'BDT',
                'lowPrice' => $range['low'],
                'highPrice' => $range['high'],
                'offerCount' => $range['count'],
                'offers' => $products->map(fn (Product $p) => [
                    '@type' => 'Offer',
                    'name' => "{$p->name} Hosting Deal",
                    'price' => $this->number($this->price($p)),
                    'priceCurrency' => 'BDT',
                ])->values()->all(),
                'priceValidUntil' => $this->validUntil(),
                'availability' => 'https://schema.org/InStock',
                'url' => $this->pageUrl($page),
            ]),
            $this->json([
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'Special Hosting Offers',
                'description' => 'Limited time web hosting deals and promotional offers',
                'itemListElement' => $products->values()->map(fn (Product $p, int $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'item' => [
                        '@type' => 'Product',
                        'name' => "{$p->name} Web Hosting Special Offer",
                        'description' => $this->description($p),
                        'image' => asset('assets/images/hostorio-domain-hosting-register.jpg'),
                        'brand' => ['@type' => 'Brand', 'name' => 'Hostorio'],
                        'offers' => [
                            '@type' => 'Offer',
                            'price' => $this->number($this->price($p)),
                            'priceCurrency' => 'BDT',
                            'priceValidUntil' => $this->validUntil(),
                            'availability' => 'https://schema.org/InStock',
                            'url' => $this->pageUrl($page),
                        ],
                    ],
                ])->all(),
            ]),
        ];
    }

    private function description(Product $p): string
    {
        $specs = collect($p->features ?? [])
            ->map(fn (array $f) => trim(($f['value'] ?? '') . ' ' . ($f['text'] ?? '')))
            ->filter()
            ->implode(', ');

        $text = Str::finish(trim((string) $p->description), '.');

        return trim(($text === '.' ? '' : $text) . ($specs !== '' ? " Includes {$specs}." : ''));
    }

    private function pageUrl(string $page): string
    {
        return Seo::url($page === 'home' ? '' : str_replace('_', '-', $page));
    }

    private function validUntil(): string
    {
        return now()->addYear()->endOfYear()->toDateString();
    }

    private function number(string $price): float | int
    {
        $value = (float) $price;

        return $value == floor($value) ? (int) $value : $value;
    }

    /** JSON safe to place inside a <script> tag. */
    private function json(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
    }
}
