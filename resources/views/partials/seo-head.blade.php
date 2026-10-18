@php
    $seo = hostorio_seo();
    $seoPage = $seo->current();
@endphp
@if ($seoPage)
    @php
        $seoTitle = $seo->title($seoPage);
        $seoDescription = $seo->description($seoPage);
        $seoCanonical = $seo->canonical($seoPage);
        $seoImage = $seo->image($seoPage);
        $socialTitle = $seo->socialTitle($seoPage);
        $socialDescription = $seo->socialDescription($seoPage);
    @endphp
    <title>{{ $seoTitle }}</title>
    @if ($seoDescription !== '')
    <meta name="description" content="{{ $seoDescription }}">
    @endif
    <meta name="robots" content="{{ $seo->robots($seoPage) }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $socialTitle }}">
    @if ($socialDescription !== '')
    <meta property="og:description" content="{{ $socialDescription }}">
    @endif
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:site_name" content="{{ $seo->siteName() }}">
    @if ($seoImage)
    <meta property="og:image" content="{{ $seoImage['url'] }}">
    @if (str_starts_with($seoImage['url'], 'https://'))
    <meta property="og:image:secure_url" content="{{ $seoImage['url'] }}">
    @endif
    @if ($seoImage['width'])
    <meta property="og:image:width" content="{{ $seoImage['width'] }}">
    <meta property="og:image:height" content="{{ $seoImage['height'] }}">
    @endif
    <meta property="og:image:alt" content="{{ $seoPage->name }}">
    @if ($seoImage['type'])
    <meta property="og:image:type" content="{{ $seoImage['type'] }}">
    @endif
    @endif
    <meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $socialTitle }}">
    @if ($socialDescription !== '')
    <meta name="twitter:description" content="{{ $socialDescription }}">
    @endif
    @if ($seoImage)
    <meta name="twitter:image" content="{{ $seoImage['url'] }}">
    @endif
@else
    <title>@yield('title', $seo->siteName())</title>
    @if ($seo->isHidden())
    <meta name="robots" content="noindex, nofollow">
    @endif
@endif
@foreach ($seo->verificationTags() as $name => $code)
    <meta name="{{ $name }}" content="{{ $code }}">
@endforeach
@if ($favicon = $seo->faviconUrl())
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
@endif
@if ($gtm = $seo->gtmId())
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtm }}');</script>
@endif
@if ($ga4 = $seo->ga4Id())
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $ga4 }}');</script>
@endif
{!! $seo->headCode() !!}
