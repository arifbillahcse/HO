@extends('layouts.app')
@push('head')
<style>
</style>
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
@section('content')
<script type="application/ld+json">
{
      "@@context": "https://schema.org",
      "@type": "Organization",
      "name": @json(hostorio_business()->name()),
      "url": "https://hostorio.com",
      "logo": "https://hostorio.com/assets/images/logo/hostorio-logo.png",
      "description": "Hostorio is a web hosting services provider in Bangladesh offering shared hosting, turbo hosting, reseller hosting, VPS, domain registration, and business email solutions.",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": @json(hostorio_business()->phone()),
        "contactType": "customer service",
        "email": @json(hostorio_business()->email()),
        "availableLanguage": ["English", "Bengali"]
      },
      "address": @json(hostorio_business()->postalAddress()),
      "sameAs": @json(hostorio_business()->sameAs())
    }
    </script>
    @foreach (app(\App\Services\ProductCatalog::class)->productSchemas('home', 'Web Hosting') as $schema)
    <script type="application/ld+json">{!! $schema !!}</script>
    @endforeach
    <script type="application/ld+json">
{
      "@@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": @json(hostorio_business()->name()),
      "image": "https://hostorio.com/assets/images/logo/hostorio-logo.png",
      "@id": "https://hostorio.com",
      "url": "https://hostorio.com",
      "telephone": @json(hostorio_business()->phone()),
      "email": @json(hostorio_business()->email()),
      "address": @json(hostorio_business()->postalAddress()),
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
        "opens": "00:00",
        "closes": "23:59"
      },
      "priceRange": "৳৳"
    }
    </script>
    <script type="application/ld+json">
{
      "@@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "https://hostorio.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Web Hosting",
          "item": "{{ url('/') }}"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Pricing",
          "item": "https://hostorio.com/#pricing"
        }
      ]
    }
    </script>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container-wrapper">
            <div class="hero-content">
                <div class="hero-left">
                    <div class="hero-tag" data-i18n="hero.tag">WWW.HOSTORIO.COM</div>
                    <h1 class="hero-title" data-i18n="hero.title">
                        Fastest Data Center<br>Web Hosting
                    </h1>
                    <p class="hero-description" data-i18n="hero.description">
                        We hosted more than 500+ happy customers. Small website to high traffic website customers. Get affordable web hosting package designed to help you start and run a successful website – regardless of your experience.
                    </p>

                    <form method="post" action="{{ hostorio_business()->clientAreaUrl() }}cart.php?a=add&domain=register" class="hero-search" role="search">
                        <input type="hidden" name="token" value="310831da269f185aeff91e84768ddf9e675287bc">
                        <label class="hero-search-field">
                            <i class="fas fa-search hero-search-icon" aria-hidden="true"></i>
                            <input
                                type="text"
                                name="sld"
                                class="hero-search-input"
                                placeholder="Search your domain name"
                                data-i18n-placeholder="hero.searchPlaceholder"
                                aria-label="Search your domain name"
                                required
                                autocomplete="off"
                            >
                        </label>
                        <button type="submit" class="hero-search-btn" data-i18n="hero.searchButton">Search</button>
                    </form>

                    <p class="hero-domains">
                        <span data-i18n="hero.domainsFrom">Domains from</span>
                        @foreach (($heroPrices ?? []) as $price)
                            <span class="hero-tld"><strong>{{ $price['tld'] }}</strong> <em>{{ $price['price'] }}/yr</em>@unless ($loop->last)<span class="hero-sep" aria-hidden="true">&middot;</span>@endunless</span>
                        @endforeach
                    </p>
                    <p class="hero-transfer">
                        <span data-i18n="hero.haveDomain">Already have a domain?</span>
                        <a href="{{ hostorio_business()->clientAreaUrl() }}cart.php?a=add&domain=transfer" data-i18n="hero.transferLink">Transfer it to Hostorio</a>
                    </p>
                </div>
                <div class="hero-right">
                    <img src="{{ asset('assets/images/hero/hostorio-cloud.png') }}" alt="Hostorio cloud hosting infrastructure with NVMe SSD storage and LiteSpeed technology in Bangladesh" class="hero-image" width="500" height="500" fetchpriority="high">
                </div>
            </div>
        </div>
    </section>
       <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container-wrapper">
            <!-- Section Header -->
            <div class="pricing-header">
                <h2 class="pricing-main-title" data-i18n="pricing.mainTitle">Find the Hosting Plan that's Right for Your Business</h2>
                <p class="pricing-subtitle" data-i18n="pricing.subtitle">We Have the Features and Service You Deserve!</p>
                <!-- Billing Toggle -->
                <div class="billing-toggle">
                    <span class="toggle-label" data-i18n="pricing.billedMonthly">Billed Monthly</span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="billingToggle" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label active" data-i18n="pricing.billedAnnually">Billed Annually</span>
                    <span class="save-badge" data-i18n="pricing.saveBadge">Save 30%!</span>
                </div>
            </div>
        <!-- Cloud Pricing Section -->
        <section class="cloud-pricing-section">
        <div class="container-wrapper">
            <!-- Pricing Cards Grid -->
            <div class="cloud-pricing-cards">
                @foreach (hostorio_products('home') as $product)
                    <x-plan-card :product="$product" page="home" />
                @endforeach
            </div>
        </div>
        </section>
        </div>
    </section>
<!-- ===========================
     DYNAMIC DOMAIN SEARCH SECTION
     =========================== -->
<section class="domain-search-section">
    <div class="container-wrapper">
        <div class="domain-search-content">
            <h2 class="section-heading">Find Your Perfect Domain Name</h2>
            
            <!-- Dynamic Search Box Form -->
            <form method="post" action="{{ hostorio_business()->clientAreaUrl() }}cart.php?a=add&domain=register" id="domain-searchform" class="search-box-wrapper">
                <!-- Hidden Token Field -->
                <input type="hidden" name="token" value="310831da269f185aeff91e84768ddf9e675287bc">
                
                <div class="search-input-container">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        type="text"
                        name="sld"
                        id="sld"
                        class="domain-input"
                        placeholder="Search for your domain name"
                        required
                        autocomplete="off"
                    >
                </div>
                <button type="submit" class="search-button">Search</button>
            </form>
            
            <!-- Domain Extensions -->
            <div class="domain-extensions">
                <div class="extension-item">
                    <span class="extension-name">.COM</span>
                </div>
                <div class="extension-item">
                    <span class="extension-name">.XYZ</span>
                </div>
                <div class="extension-item">
                    <span class="extension-name">.TOP</span>
                </div>                
                <div class="extension-item">
                    <span class="extension-name">.SHOP</span>
                </div>
                <div class="extension-item">
                    <span class="extension-name">.INFO</span>
                </div>
            </div>
        </div>
    </div>
</section>
        <!-- Services Section -->
    <section class="services-section">
        <div class="container-wrapper">
            <!-- Section Header -->
            <div class="section-header">
                <h2 class="section-main-title" data-i18n="services.sectionTitle">Your Complete Roadmap to Digital Growth</h2>
                <p class="section-subtitle" data-i18n="services.sectionSubtitle">Hosting is just the engine—we provide the whole vehicle.</p>
            </div>
            <!-- Services List -->
            <div class="services-list">
                <!-- Service Item 1 -->
                <div class="service-item">
                    <div class="service-image">
                        <img src="{{ asset('assets/images/hostorio-domain-hosting-register.jpg') }}" alt="Hostorio domain registration – find and secure your perfect domain name online">
                    </div>
                    <div class="service-content">
                        <h3 class="service-title" data-i18n="services.item1.title">Secure Your Digital Identity</h3>
                        <p class="service-description" data-i18n="services.item1.desc">
                            Grab the Perfect Domain Your domain is more than just an address; it's your brand's first impression. Use our lightning-fast search tool to find the perfect name that defines your business. Whether you're starting fresh or want to move an existing domain to a more reliable home, our seamless transfer process makes it effortless.
                        </p>
                        <a href="{{ hostorio_business()->clientAreaUrl() }}cart.php?a=add&domain=register" class="service-link">
                            <span data-i18n="services.item1.link">Explore Domain</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                </div>
                <!-- Service Item 2 -->
                <div class="service-item reverse">
                    <div class="service-image">
                        <img src="{{ asset('assets/images/hostorio-performance.webp') }}" alt="Hostorio high-performance web hosting powered by LiteSpeed servers and NVMe SSD storage">
                    </div>
                    <div class="service-content">
                        <h3 class="service-title" data-i18n="services.item2.title">Performance-First Web Hosting</h3>
                        <p class="service-description" data-i18n="services.item2.desc">
                            The Right Foundation for Every Project Whether you need the simplicity of Shared Hosting, the raw power of a VPS, or a specialized WordPress environment, we've got you covered. Powered by NVMe storage and LiteSpeed Web Servers, our infrastructure is built to ensure your site stays fast, stable, and ready for high traffic.
                        </p>
                        <a href="" class="service-link">
                            <span data-i18n="services.item2.link">Choose a hosting</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                </div>
                <!-- Service Item 3 -->
                <div class="service-item">
                    <div class="service-image">
                        <img src="{{ asset('assets/images/hostorio-email-solution.webp') }}" alt="Hostorio professional business email hosting with custom domain – name@yourdomain.com">
                    </div>
                    <div class="service-content">
                        <h3 class="service-title" data-i18n="services.item3.title">Professional Email Solutions</h3>
                        <p class="service-description" data-i18n="services.item3.desc">
                            Build Trust with Every Inbox Move beyond generic email addresses and earn instant credibility with a custom address (name@yourdomain.com). Our secure, ad-free business email plans keep your communications organized, private, and professional, complete with integrated tools to keep your team in sync.
                        </p>
                        <a href="" class="service-link">
                            <span data-i18n="services.item3.link">Business Email Plans</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                </div>
                <!-- Service Item 4 -->
                <div class="service-item reverse">
                    <div class="service-image">
                        <img src="{{ asset('assets/images/hostorio-ssl-security.webp') }}" alt="Hostorio free SSL certificate and DDoS protection keeping websites secure 24/7">
                    </div>
                    <div class="service-content">
                        <h3 class="service-title" data-i18n="services.item4.title">Bulletproof Security</h3>
                        <p class="service-description" data-i18n="services.item4.desc">
                            Protection for You and Your Visitors We take security seriously so you don't have to. Every Hostorio account comes standard with Free SSL certificates, BitNinja Server Security, and automated malware scanning. With 2-Factor Authentication (2FA) and premium DNS protection, your data—and your customers' trust—remains safe.
                        </p>
                        <a href="" class="service-link">
                            <span data-i18n="services.item4.link">Secure yourself</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                </div>
                <!-- Service Item 5 -->
                <div class="service-item">
                    <div class="service-image">
                        <img src="{{ asset('assets/images/enterprice-security.jpg') }}" alt="Hostorio enterprise-grade security with free SSL, DDoS protection via BitNinja and automated daily backups">
                    </div>
                    <div class="service-content">
                        <h3 class="service-title" data-i18n="services.item5.title">Enterprise-Grade Security, Affordable Prices</h3>
                        <p class="service-description" data-i18n="services.item5.desc">
                            Security isn't luxury—it's essential. Every Hostorio account includes free SSL certificates, DDoS protection via BitNinja, automatic malware scanning, and daily automated backups. We take the stress out of security so you can focus on growing your business. Sleep soundly knowing your data and your customers' data are protected 24/7.
                        </p>
                        <a href="" class="service-link">
                            <span data-i18n="services.item5.link">Learn about security</span>
                            <i class="arrow-icon">→</i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
       <!-- WordPress Hosting Section -->
    <section class="wordpress-section">
        <div class="container-wrapper">
            <div class="wordpress-content">
                <!-- Left Side - Graphic -->
                <div class="wordpress-graphic">
                    <!-- Browser Window -->
                    <div class="browser-window">
                        <div class="browser-header">
                            <span class="browser-dot"></span>
                            <span class="browser-dot"></span>
                            <span class="browser-dot"></span>
                        </div>
                        <div class="browser-content">
                            <!-- Hero Section -->
                            <div class="wp-hero">
                                <h3 class="wp-blog-title" data-i18n="wordpress.blogTitle">Your Blog Title</h3>
                            </div>
                            <!-- Latest Post Section -->
                            <div class="wp-posts-section">
                                <p class="wp-section-title" data-i18n="wordpress.latestPost">Latest Post</p>
                                <div class="wp-posts-grid">
                                    <div class="wp-post-card">
                                        <div class="wp-post-image"></div>
                                        <div class="wp-post-lines">
                                            <span class="line"></span>
                                            <span class="line short"></span>
                                        </div>
                                    </div>
                                    <div class="wp-post-card">
                                        <div class="wp-post-image"></div>
                                        <div class="wp-post-lines">
                                            <span class="line"></span>
                                            <span class="line short"></span>
                                        </div>
                                    </div>
                                    <div class="wp-post-card add-new">
                                        <div class="wp-post-image faded"></div>
                                        <div class="wp-post-lines">
                                            <span class="line faded"></span>
                                            <span class="line short faded"></span>
                                        </div>
                                        <div class="add-icon">+</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Floating Elements -->
                    <div class="floating-element top-element">
                        <div class="theme-preview">
                            <div class="theme-grid">
                                <div class="theme-box orange"></div>
                                <div class="theme-box-row">
                                    <div class="theme-box small orange"></div>
                                    <div class="theme-box small orange"></div>
                                </div>
                            </div>
                            <div class="theme-grid">
                                <div class="theme-box gray"></div>
                                <div class="theme-box gray"></div>
                            </div>
                            <div class="theme-grid">
                                <div class="theme-box-list">
                                    <span class="theme-line gray"></span>
                                    <span class="theme-line gray"></span>
                                </div>
                                <div class="theme-box-list">
                                    <span class="theme-line gray"></span>
                                    <span class="theme-line gray"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="floating-element left-element">
                        <div class="dashboard-menu">
                            <div class="menu-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="3" width="7" height="7" fill="#FF7800"/>
                                    <rect x="3" y="14" width="7" height="7" fill="#FF7800"/>
                                    <rect x="14" y="3" width="7" height="7" fill="#FF7800"/>
                                    <rect x="14" y="14" width="7" height="7" fill="#FF7800"/>
                                </svg>
                            </div>
                            <ul class="menu-list">
                                <li>Dashboard</li>
                                <li>Posts</li>
                                <li>Media</li>
                                <li>Pages</li>
                                <li>Comments</li>
                                <li>Appearance</li>
                                <li>Plugins</li>
                                <li>Users</li>
                                <li>Tools</li>
                                <li>Settings</li>
                            </ul>
                        </div>
                    </div>
                    <div class="floating-element bottom-element">
                        <div class="editor-toolbar">
                            <svg width="100%" height="40" viewBox="0 0 200 40" fill="none">
                                <circle cx="20" cy="20" r="8" fill="#0170B9"/>
                                <circle cx="50" cy="20" r="8" fill="#0170B9"/>
                                <circle cx="80" cy="20" r="8" fill="#0170B9"/>
                                <circle cx="110" cy="20" r="8" fill="#0170B9"/>
                                <circle cx="140" cy="20" r="8" fill="#0170B9"/>
                                <circle cx="170" cy="20" r="8" fill="#0170B9"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Right Side - Content -->
                <div class="wordpress-text">
                    <h2 class="wordpress-title" data-i18n="wordpress.title">WordPress Hosting Turbocharged</h2>
                    <p class="wordpress-description" data-i18n="wordpress.description">
                       WordPress powers the web, but we power WordPress. We provide a managed environment that offers the perfect balance of raw power and complete control. Whether you are a solo blogger or a large-scale agency, our infrastructure is fine-tuned to ensure your WordPress site is faster, more secure, and easier to manage than ever before.
                    </p>
                    <!-- Features List -->
                    <div class="features-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                    <circle cx="24" cy="24" r="20" fill="#FF7800" opacity="0.2"/>
                                    <path d="M17 20L22 28L32 18" stroke="#FF7800" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 class="feature-title" data-i18n="wordpress.feature1">1-Click Script Deployment</h3>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                    <circle cx="24" cy="24" r="20" fill="#0170B9" opacity="0.2"/>
                                    <path d="M24 12L28 20L36 21L30 27L32 36L24 31L16 36L18 27L12 21L20 20L24 12Z" stroke="#0170B9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h3 class="feature-title" data-i18n="wordpress.feature2">LiteSpeed-Optimized Stack</h3>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                                    <circle cx="24" cy="24" r="20" fill="#FF7800" opacity="0.2"/>
                                    <path d="M24 8V16M24 32V40M35 24H27M21 24H13M33 13L28 18M28 30L33 35M13 13L18 18M18 30L13 35" stroke="#FF7800" stroke-width="2.5" stroke-linecap="round"/>
                                    <circle cx="24" cy="24" r="6" stroke="#FF7800" stroke-width="2.5"/>
                                </svg>
                            </div>
                            <h3 class="feature-title" data-i18n="wordpress.feature3">Proactive Malware Defense</h3>
                        </div>
                    </div>
                    <!-- CTA Button -->
                    <div class="wordpress-cta">
                        <a href="" class="cta-button" data-i18n="wordpress.cta">Get Started Now</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Support Section -->
    <section class="support-section">
        <div class="container-wrapper">
            <!-- Section Header -->
            <div class="section-header">
                <h2 class="section-main-title" data-i18n="support.sectionTitle">Customer Success Focus</h2>
                <p class="section-subtitle" data-i18n="support.sectionSubtitle">We provide everything you need to succeed online</p>
            </div>
            <!-- Support Cards -->
            <div class="support-cards">
                <!-- Card 1 -->
                <div class="support-card">
                    <div class="card-image-wrapper">
                        <img src="{{ asset('assets/images/support/support-hostorio.webp') }}" alt="Hostorio 24/7 customer support team guiding new users through web hosting setup and configuration" class="card-image">
                        <div class="image-overlay blue-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title" data-i18n="support.card1.title">Onboarding Support</h3>
                        <p class="card-description" data-i18n="support.card1.desc">
                            Don't know where to start? Our step-by-step tutorials and 24/7 support team walk you through domain setup, hosting configuration, and WordPress installation.
                        </p>
                        <a href="{{ hostorio_business()->clientAreaUrl() }}submitticket.php" class="card-button" data-i18n="support.card1.btn">Get started</a>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="support-card">
                    <div class="card-image-wrapper">
                        <img src="{{ asset('assets/images/support/blog-hostorio.webp') }}" alt="Hostorio knowledge base and resource center with web hosting tutorials, guides and security tips" class="card-image">
                        <div class="image-overlay green-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title" data-i18n="support.card2.title">Discover our Resources</h3>
                        <p class="card-description" data-i18n="support.card2.desc">
                            Access our resource center for hosting best practices, security tips, performance optimization guides, and industry insights.
                        </p>
                        <a href="{{ hostorio_business()->clientAreaUrl() }}knowledgebase" class="card-button" data-i18n="support.card2.btn">Get learning</a>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="support-card">
                    <div class="card-image-wrapper">
                        <img src="{{ asset('assets/images/support/trasfer-hosting-hostorio.webp') }}" alt="Hassle-free domain and web hosting transfer service to Hostorio – fast, easy migration" class="card-image">
                        <div class="image-overlay orange-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title" data-i18n="support.card3.title">Hassle-Free Transfers</h3>
                        <p class="card-description" data-i18n="support.card3.desc">
                            Move your domain and Hosting to Us. Switching hosts shouldn't feel like moving houses. With us, it's simple.
                        </p>
                        <a href="{{ hostorio_business()->clientAreaUrl() }}cart.php?a=add&domain=transfer" class="card-button" data-i18n="support.card3.btn">Transfer today</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
