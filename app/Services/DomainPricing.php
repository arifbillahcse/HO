<?php

namespace App\Services;

use App\Models\DomainExtension;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Domain prices for the Domain Registration page: live from WHMCS, in the order,
 * visibility and badges set under Admin → Domains → Domain prices.
 */
class DomainPricing
{
    /** Shown first, in this order, until the admin reorders the list. */
    public const PREFERRED_ORDER = ['.com', '.com.bd', '.net', '.org', '.xyz', '.info', '.bd', '.net.bd', '.org.bd', '.co', '.io', '.online', '.store', '.shop', '.site', '.top'];

    public const DEFAULT_FAQ = [
        ['question' => 'How much does a .com domain cost in Bangladesh?', 'answer' => 'At Hostorio a .com domain costs {register:.com} for the first year and {renew:.com} a year to renew. Every extension\'s registration, renewal and transfer price is in the table above, live from our billing system.'],
        ['question' => 'How do I register a .com.bd domain?', 'answer' => 'Search for the name above and add it to the cart. .com.bd domains are registered through BTCL, the Bangladesh registry, which can ask for documents such as a National ID or trade licence. Our team guides you through the paperwork after you order.'],
        ['question' => 'Can I pay for a domain with bKash or Nagad?', 'answer' => 'Yes. You can pay with bKash, Nagad, or a Visa or Mastercard card when you check out.'],
        ['question' => 'How long does it take before my domain works?', 'answer' => 'Most domains, such as .com, .net and .xyz, are active within minutes of payment. .com.bd domains take longer because the registry processes them by hand.'],
        ['question' => 'Can I use my domain with hosting from another company?', 'answer' => 'Yes. Point the domain\'s nameservers to any host from your client area. When you host with Hostorio, the domain and hosting are managed in one place.'],
        ['question' => 'What happens when my domain is about to expire?', 'answer' => 'We email you renewal reminders before the expiry date, and you can renew in a few clicks from your client area so your website and email keep working.'],
        ['question' => 'Can I transfer my domain to Hostorio?', 'answer' => 'Yes. Unlock the domain at your current registrar, get its EPP (transfer) code and start the transfer with us. The transfer price usually includes a one-year extension.'],
    ];

    /** @var list<array>|null memo of all() for this request */
    private ?array $all = null;

    public function __construct(private WhmcsService $whmcs)
    {
    }

    /** @return array{currency: array, details: array<string, array>}|null */
    private function pricing(): ?array
    {
        try {
            $pricing = $this->whmcs->pricing();
        } catch (Throwable $e) {
            Log::warning('Domain prices unavailable: ' . $e->getMessage());

            return null;
        }

        return isset($pricing['details']) && $pricing['details'] !== [] ? $pricing : null;
    }

    public function isAvailable(): bool
    {
        return $this->pricing() !== null;
    }

    /** Every extension WHMCS sells, with its admin settings, in display order (hidden ones included). */
    public function all(): array
    {
        return $this->all ??= $this->build();
    }

    private function build(): array
    {
        $pricing = $this->pricing();

        if ($pricing === null) {
            return [];
        }

        $settings = DomainExtension::query()->get()->keyBy('tld');
        $rows = [];

        foreach ($pricing['details'] as $tld => $prices) {
            $setting = $settings[$tld] ?? null;
            $badge = $setting?->badge ?? (in_array($prices['group'], ['hot', 'new', 'sale'], true) ? $prices['group'] : 'none');

            $rows[] = [
                'tld' => $tld,
                'visible' => $setting?->is_visible ?? true,
                'sort' => $setting?->sort_order ?? $this->defaultSort($tld, $prices['popular']),
                'badge' => $badge === 'none' ? null : $badge,
                'register' => $this->money($prices['register'], $pricing['currency']),
                'renew' => $this->money($prices['renew'], $pricing['currency']),
                'transfer' => $this->money($prices['transfer'], $pricing['currency']),
            ];
        }

        usort($rows, fn ($a, $b) => [$a['sort'], $a['tld']] <=> [$b['sort'], $b['tld']]);

        return $rows;
    }

    /** The extensions shown on the website. */
    public function rows(): array
    {
        return array_values(array_filter($this->all(), fn (array $row) => $row['visible']));
    }

    /** One price, e.g. price('.com', 'renew'), or null when WHMCS has none. */
    public function price(string $tld, string $type = 'register'): ?array
    {
        foreach ($this->all() as $row) {
            if ($row['tld'] === WhmcsService::normalizeTld($tld)) {
                return $row[$type] ?? null;
            }
        }

        return null;
    }

    /**
     * Make sure every extension WHMCS sells has a row in the admin list.
     *
     * @return int how many were added
     */
    public function sync(): int
    {
        $existing = DomainExtension::query()->pluck('tld')->all();
        $added = 0;

        foreach ($this->all() as $row) {
            if (! in_array($row['tld'], $existing, true)) {
                DomainExtension::create(['tld' => $row['tld'], 'sort_order' => $row['sort'], 'is_visible' => true]);
                $added++;
            }
        }

        $this->all = null;

        return $added;
    }

    private function defaultSort(string $tld, bool $popular): int
    {
        $index = array_search($tld, self::PREFERRED_ORDER, true);

        return $index !== false ? $index : ($popular ? 100 : 1000);
    }

    /**
     * BDT for the page text, plus the USD amount the site's currency switch converts from.
     *
     * @return array{bdt: string, usd: float}|null
     */
    private function money(?float $amount, array $currency): ?array
    {
        if ($amount === null) {
            return null;
        }

        $rate = (float) SettingsService::get('exchange_rate', 125) ?: 125;
        $isBdt = strtoupper((string) ($currency['code'] ?? '')) === 'BDT';
        $bdt = $isBdt ? $amount : $amount * $rate;

        return [
            'bdt' => '৳' . number_format(round($bdt)),
            'usd' => round($isBdt ? $amount / $rate : $amount, 4),
        ];
    }

    /* ───────────────────────── FAQ ───────────────────────── */

    /** @return list<array{question: string, answer: string}> with {register:.com} style placeholders filled in */
    public function faq(): array
    {
        $items = SettingsService::get('domain_faq');
        $items = is_array($items) && $items !== [] ? $items : self::DEFAULT_FAQ;

        return array_values(array_filter(array_map(fn ($item) => [
            'question' => trim((string) ($item['question'] ?? '')),
            'answer' => $this->fillPrices(trim((string) ($item['answer'] ?? ''))),
        ], $items), fn ($item) => $item['question'] !== '' && $item['answer'] !== ''));
    }

    /** "{register:.com}" → "৳1,499"; prices WHMCS does not have read "see our price list". */
    public function fillPrices(string $text): string
    {
        return preg_replace_callback('/\{(register|renew|transfer):\s*(\.?[a-z0-9.\-]+)\}/i', function ($m) {
            $price = $this->price($m[2], strtolower($m[1]));

            return $price['bdt'] ?? 'see our price list';
        }, $text);
    }
}
