@extends('layouts.app')

@section('title', 'Hostorio – Fast Web Hosting')

@push('meta')
<meta name="description" content="Get fast web hosting from ৳999/year with free SSL, free domain registration, LiteSpeed servers & 24/7 expert support. 99.9% uptime guaranteed. Start today!">
<link rel="canonical" href="{{ url('/') }}">
<meta name="robots" content="index, follow">
<meta property="og:type" content="website">
<meta property="og:title" content="Fast Web Hosting ৳999/yr | Free SSL & Domain | Hostorio">
<meta property="og:description" content="Fast web hosting from ৳999/year with free SSL, free domain, LiteSpeed servers & 24/7 expert support. 99.9% uptime. Trusted by 500+ Bangladesh businesses.">
<meta property="og:url" content="{{ url('/') }}">
@endpush

@push('translations')
<script src="{{ asset('assets/js/translations/header-footer/header.js') }}"></script>
<script src="{{ asset('assets/js/translations/Home/hero.js') }}"></script>
<script src="{{ asset('assets/js/translations/Home/pricing.js') }}"></script>
<script src="{{ asset('assets/js/translations/Home/domain.js') }}"></script>
<script src="{{ asset('assets/js/translations/Home/services.js') }}"></script>
<script src="{{ asset('assets/js/translations/Home/wordpress.js') }}"></script>
<script src="{{ asset('assets/js/translations/Home/support.js') }}"></script>
<script src="{{ asset('assets/js/translations/header-footer/footer.js') }}"></script>
@endpush

<div class="container-wrapper">
    <section class="hero-section">
        <h1 data-i18n="hero.title">Fast, Affordable Web Hosting for Everyone</h1>
        <p data-i18n="hero.subtitle">Get started with web hosting from just ৳999/year</p>
        <p>This is a placeholder for the home page content.</p>
    </section>
</div>
