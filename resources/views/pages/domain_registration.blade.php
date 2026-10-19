@extends('layouts.app')
@push('head')
<link rel="stylesheet" href="{{ asset('assets/css/domains.css') }}">
@endpush
@push('translations')
<script src="{{ asset('assets/js/translations/header-footer/header.js') }}"></script>
<script src="{{ asset('assets/js/translations/domain/registration.js') }}"></script>
<script src="{{ asset('assets/js/translations/header-footer/footer.js') }}"></script>
@endpush
@push('scripts')
<script src="{{ asset('assets/js/domains.js') }}" defer></script>
@endpush
@php
    $domains = app(\App\Services\DomainPricing::class);
    $rows = $domains->rows();
    $faq = $domains->faq();
    $featured = collect(app(\App\Services\WhmcsService::class)->heroTlds())
        ->map(fn ($tld) => collect($rows)->firstWhere('tld', $tld))
        ->filter(fn ($row) => $row && $row['register'])
        ->values();
    $cart = hostorio_business()->clientAreaUrl() . 'cart.php?a=add&domain=';
@endphp
@section('content')
@include('partials.schema.organization')
<script type="application/ld+json">{!! \App\Services\LocalSeo::json([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => hostorio_url()],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Domain Registration', 'item' => hostorio_url('domain-registration')],
    ],
]) !!}</script>
@if ($faq !== [])
<script type="application/ld+json">{!! \App\Services\LocalSeo::json([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn ($item) => [
        '@type' => 'Question',
        'name' => $item['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
    ], $faq),
]) !!}</script>
@endif

<!-- ===================== Hero: domain search ===================== -->
<section class="dr-hero">
    <div class="container-wrapper">
        <div class="dr-hero-inner">
            <span class="dr-hero-tag" data-i18n="domainReg.hero.tag">Domain Registration</span>
            <h1 class="dr-hero-title" data-i18n="domainReg.hero.title">Domain Registration in Bangladesh</h1>
            <p class="dr-hero-subtitle" data-i18n="domainReg.hero.subtitle">Register .com, .com.bd, .net and many more at live prices. Pay with bKash, Nagad or card, and manage your domain and hosting in one place.</p>

            <form method="post" action="{{ $cart }}register" class="hero-search dr-search" id="dr-search" role="search">
                <input type="hidden" name="token" value="310831da269f185aeff91e84768ddf9e675287bc">
                <label class="hero-search-field">
                    <i class="fas fa-search hero-search-icon" aria-hidden="true"></i>
                    <input
                        type="text"
                        name="sld"
                        id="dr-search-input"
                        class="hero-search-input"
                        placeholder="Find your perfect domain name"
                        data-i18n-placeholder="domainReg.hero.placeholder"
                        aria-label="Find your perfect domain name"
                        required
                        autocomplete="off"
                    >
                </label>
                <button type="submit" class="hero-search-btn" data-i18n="domainReg.hero.button">Search</button>
            </form>

            @if ($featured->isNotEmpty())
            <p class="dr-hero-popular">
                <span data-i18n="domainReg.hero.popular">Popular:</span>
                @foreach ($featured as $row)
                    <span class="dr-hero-tld"><strong>{{ $row['tld'] }}</strong> <em><span data-currency-symbol>৳</span><span class="cloud-amount" data-auto-price="{{ $row['register']['usd'] }}">{{ ltrim($row['register']['bdt'], '৳') }}</span></em><span class="dr-hero-per" data-i18n="domainReg.prices.perYear">/yr</span></span>
                @endforeach
            </p>
            @endif
            <p class="dr-hero-transfer">
                <span data-i18n="domainReg.hero.haveDomain">Already have a domain?</span>
                <a href="{{ $cart }}transfer" data-i18n="domainReg.hero.transferLink">Transfer it to Hostorio</a>
            </p>
        </div>
    </div>
</section>

<!-- ===================== Price table ===================== -->
<section class="dr-prices" id="domain-prices">
    <div class="container-wrapper">
        <div class="dr-section-head">
            <h2 class="dr-section-title" data-i18n="domainReg.prices.title">Domain Prices</h2>
            <p class="dr-section-subtitle" data-i18n="domainReg.prices.subtitle">Live domain registration, renewal and transfer prices for every extension we sell, per year.</p>
        </div>

        @if ($rows === [])
            <div class="dr-unavailable">
                <p data-i18n="domainReg.prices.unavailable">Live domain prices are being updated. You can see every price in our client area.</p>
                <a href="{{ $cart }}register" class="dr-btn dr-btn-primary" data-i18n="domainReg.prices.unavailableLink">View domain prices</a>
            </div>
        @else
            <div class="dr-filter">
                <i class="fas fa-filter" aria-hidden="true"></i>
                <input type="search" id="dr-filter-input" placeholder="Search an extension, e.g. .com.bd" data-i18n-placeholder="domainReg.prices.filter" aria-label="Search an extension">
            </div>

            <div class="dr-table-wrap">
                <table class="dr-table">
                    <thead>
                        <tr>
                            <th scope="col" data-i18n="domainReg.prices.colExt">Extension</th>
                            <th scope="col" data-i18n="domainReg.prices.colRegister">Register</th>
                            <th scope="col" data-i18n="domainReg.prices.colRenew">Renew</th>
                            <th scope="col" data-i18n="domainReg.prices.colTransfer">Transfer</th>
                            <th scope="col"><span class="dr-visually-hidden">Order</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                        <tr class="dr-row" data-tld="{{ $row['tld'] }}">
                            <th scope="row" class="dr-ext">
                                <span class="dr-tld">{{ $row['tld'] }}</span>
                                @if ($row['badge'])
                                    <span class="dr-badge dr-badge-{{ $row['badge'] }}" data-i18n="domainReg.prices.{{ $row['badge'] }}">{{ strtoupper($row['badge']) }}</span>
                                @endif
                            </th>
                            @foreach (['register' => 'colRegister', 'renew' => 'colRenew', 'transfer' => 'colTransfer'] as $type => $label)
                            <td class="dr-cell dr-cell-{{ $type }}">
                                <span class="dr-cell-label" data-i18n="domainReg.prices.{{ $label }}">{{ ucfirst($type) }}</span>
                                @if ($row[$type])
                                    <span class="dr-price"><span data-currency-symbol>৳</span><span class="cloud-amount" data-auto-price="{{ $row[$type]['usd'] }}">{{ ltrim($row[$type]['bdt'], '৳') }}</span><span class="dr-per" data-i18n="domainReg.prices.perYear">/yr</span></span>
                                @else
                                    <span class="dr-price dr-price-none">—</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="dr-cell dr-cell-action">
                                @if ($row['register'])
                                    <a href="{{ $cart }}register" class="dr-btn dr-btn-buy" data-tld="{{ $row['tld'] }}" data-i18n="domainReg.prices.buy">Register</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="dr-no-match" id="dr-no-match" hidden data-i18n="domainReg.prices.noMatch">No extension matches your search.</p>
            </div>
            <p class="dr-note" data-i18n="domainReg.prices.note">The renewal price applies from the second year. Switch between BDT and USD at the top of the page.</p>
        @endif
    </div>
</section>

<!-- ===================== Why Hostorio ===================== -->
<section class="dr-why">
    <div class="container-wrapper">
        <div class="dr-section-head">
            <h2 class="dr-section-title" data-i18n="domainReg.why.title">Why register your domain with Hostorio</h2>
            <p class="dr-section-subtitle" data-i18n="domainReg.why.subtitle">Everything you need to put your name online, with support from a local team.</p>
        </div>
        <div class="dr-why-grid">
            @foreach ([
                'dns' => ['fa-sliders', 'DNS management', 'Point your domain to any website or email service and edit its DNS records from your client area.'],
                'pay' => ['fa-wallet', 'Pay with bKash, Nagad or card', 'Local payment methods, so you can buy in minutes without an international card.'],
                'lock' => ['fa-lock', 'Domain lock and EPP code', 'Your domain stays locked against unauthorised transfers, and you can get its EPP code whenever you need it.'],
                'support' => ['fa-headset', '24/7 local support', 'Our team in Bangladesh helps with registration, DNS settings and .com.bd paperwork.'],
                'together' => ['fa-layer-group', 'Domain and hosting together', 'Manage your domain, hosting, SSL and email from one client area.'],
                'renew' => ['fa-bell', 'Renewal reminders', 'We email you before your domain expires, so your website never goes offline by surprise.'],
            ] as $key => [$icon, $title, $text])
            <div class="dr-why-card">
                <span class="dr-why-icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></span>
                <h3 class="dr-why-title" data-i18n="domainReg.why.{{ $key }}Title">{{ $title }}</h3>
                <p class="dr-why-text" data-i18n="domainReg.why.{{ $key }}Text">{{ $text }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===================== Steps ===================== -->
<section class="dr-steps">
    <div class="container-wrapper">
        <div class="dr-section-head">
            <h2 class="dr-section-title" data-i18n="domainReg.steps.title">Register a domain in 3 steps</h2>
        </div>
        <ol class="dr-steps-list">
            @foreach ([
                1 => ['Search', 'Type the name you want and see right away if it is available.'],
                2 => ['Add to cart and pay', 'Choose how many years, then pay with bKash, Nagad or card.'],
                3 => ['Manage it', 'Your domain appears in your client area, ready to connect to your website and email.'],
            ] as $step => [$title, $text])
            <li class="dr-step">
                <span class="dr-step-number" aria-hidden="true">{{ $step }}</span>
                <h3 class="dr-step-title" data-i18n="domainReg.steps.s{{ $step }}Title">{{ $title }}</h3>
                <p class="dr-step-text" data-i18n="domainReg.steps.s{{ $step }}Text">{{ $text }}</p>
            </li>
            @endforeach
        </ol>
    </div>
</section>

<!-- ===================== FAQ (edited in Admin → Domains → Domain page) ===================== -->
@if ($faq !== [])
<section class="faq-section">
    <div class="container-wrapper">
        <div class="faq-header">
            <h2 class="faq-title" data-i18n="domainReg.faq.title">Domain registration FAQ</h2>
            <p class="faq-description" data-i18n="domainReg.faq.description">Answers to common questions about buying a domain in Bangladesh.</p>
        </div>
        <div class="faq-accordion">
            @foreach ($faq as $item)
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <div class="faq-question-content">
                        <i class="fas fa-circle-question question-icon"></i>
                        <h3 class="faq-question-text">{{ $item['question'] }}</h3>
                    </div>
                    <i class="fas fa-chevron-down faq-arrow"></i>
                </div>
                <div class="faq-answer">
                    <p>{!! nl2br(e($item['answer']), false) !!}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ===================== Call to action ===================== -->
<section class="dr-cta">
    <div class="container-wrapper">
        <div class="dr-cta-box">
            <div>
                <h2 class="dr-cta-title" data-i18n="domainReg.cta.title">Need hosting for your new domain?</h2>
                <p class="dr-cta-text" data-i18n="domainReg.cta.text">Fast NVMe web hosting with free SSL. Put your website online today.</p>
            </div>
            <div class="dr-cta-actions">
                <a href="{{ hostorio_url('web-hosting') }}" class="dr-btn dr-btn-primary" data-i18n="domainReg.cta.hosting">See hosting plans</a>
                <a href="{{ $cart }}transfer" class="dr-btn dr-btn-ghost" data-i18n="domainReg.cta.transfer">Transfer a domain</a>
            </div>
        </div>
    </div>
</section>
@endsection
