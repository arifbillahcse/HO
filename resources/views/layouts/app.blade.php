<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hostorio')</title>

    @stack('meta')

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">

    @stack('head')
</head>
<body>
    @include('partials.header')

    <main class="site-content">
        @yield('content')
    </main>

    @include('partials.footer')

    <!-- Translation system -->
    <script src="{{ asset('assets/js/translations.js') }}"></script>

    <!-- Page-specific translation files -->
    @stack('translations')

    <!-- i18n and utilities -->
    <script src="{{ asset('assets/js/i18n.js') }}"></script>
    <script>
        window.hostorioSettings = {
            exchangeRate: {{ hostorio('exchange_rate', 125) }},
            themeUri: '{{ asset('assets') }}'
        };
    </script>

    <!-- Core scripts -->
    <script src="{{ asset('assets/js/header.js') }}"></script>
    <script src="{{ asset('assets/js/utils.js') }}"></script>
    <script src="{{ asset('assets/js/pricing.js') }}"></script>
    <script src="{{ asset('assets/js/billing.js') }}"></script>
    <script src="{{ asset('assets/js/animations.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('scripts')
</body>
</html>
