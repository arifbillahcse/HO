@extends('layouts.app')
@section('title', 'Special Offers – Hostorio Web Hosting')
@push('meta')
<meta name="description" content="Hostorio - Special Offers. Fast, reliable web hosting services.">
<link rel="canonical" href="{{ url('/offer') }}">
<meta name="robots" content="index, follow">
<meta property="og:type" content="website">
<meta property="og:title" content="Special Offers – Hostorio">
<meta property="og:description" content="Hostorio - Special Offers">
<meta property="og:url" content="{{ url('/offer') }}">
@endpush
@push('head')
<style>
</style>
@endpush
@push('translations')
<script src="{{ asset('assets/js/translations/header-footer/header.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/hero.js') }}"></script>
<script src="{{ asset('assets/js/translations/offer/pricing.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/features.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/comparison.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/testimonial.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/faq.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/wordpress.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/support.js') }}"></script>
<script src="{{ asset('assets/js/translations/header-footer/footer.js') }}"></script>
@endpush
@section('content')
<script type="application/ld+json">
{
      "@@context": "https://schema.org",
      "@type": "Organization",
      "name": "Hostorio",
      "url": "https://hostorio.com",
      "logo": "https://hostorio.com/assets/images/logo/hostorio-logo.png",
      "description": "Hostorio offers limited-time special web hosting deals and promotional offers in Bangladesh. Save big on hosting packages with free domain and SSL.",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+8801779440297",
        "contactType": "customer service",
        "email": "mail@hostorio.com",
        "availableLanguage": ["English", "Bengali"],
        "areaServed": "BD"
      },
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "H-161, OABDA SOROK MATHBARIA",
        "addressLocality": "Kanchan, Rupganj",
        "addressRegion": "Narayanganj",
        "addressCountry": "BD"
      },
      "sameAs": [
        "https://www.facebook.com/hostorio",
        "https://x.com/hostorio",
        "https://www.youtube.com/@arifbillahcse",
        "https://www.linkedin.com/company/hostorio/"
      ]
    }
    </script>
    @foreach (app(\App\Services\ProductCatalog::class)->offerSchemas('offer') as $schema)
    <script type="application/ld+json">{!! $schema !!}</script>
    @endforeach
    <script type="application/ld+json">
{
      "@@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "How can I buy web hosting?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Simply select your preferred hosting offer plan, click 'Order Now', complete the checkout process, and our team will set up your hosting immediately. These special offers are available for a limited time."
          }
        },
        {
          "@type": "Question",
          "name": "Which web hosting plan do I need to choose?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "For personal blogs, choose 2GB+.SHOP domain. For small businesses, 5GB+XYZ is ideal. For eCommerce or businesses needing a domain, our recommended 10GB+.COM deal is the best choice. For growing businesses, select the 20GB+.COM offer."
          }
        },
        {
          "@type": "Question",
          "name": "Can I upgrade my hosting package?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes, you can upgrade your hosting package anytime from your client area or contact our 24/7 support team for assistance."
          }
        },
        {
          "@type": "Question",
          "name": "Can you help me get started?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Absolutely! Our 24/7 support team provides complete onboarding assistance including domain setup, WordPress installation, email configuration, and getting your website online."
          }
        },
        {
          "@type": "Question",
          "name": "Can I transfer my website to your web hosting?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes! We offer free hassle-free website migration. Our technical team will transfer your website from your current host to Hostorio at no additional cost when you purchase any of these special offers."
          }
        },
        {
          "@type": "Question",
          "name": "Will you help me if I have any problem with hosting?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes, our expert support team is available 24/7 via live chat, email, and ticket system to resolve any hosting issues promptly. All special offer plans include full support."
          }
        }
      ]
    }
    </script>!-- ===========================
         PACKAGE CHOOSER SECTION
         =========================== -->
    <div class="package-chooser-wrapper">
        <!-- Background Animation -->
        <div class="motion-background">
            <i class="fas fa-server motion-item item-1"></i>
            <i class="fas fa-cloud motion-item item-2"></i>
            <i class="fas fa-database motion-item item-3"></i>
            <i class="fas fa-shield-halved motion-item item-4"></i>
            <i class="fas fa-rocket motion-item item-5"></i>
            <i class="fas fa-bolt motion-item item-6"></i>
            <i class="fas fa-globe motion-item item-7"></i>
            <i class="fas fa-envelope motion-item item-8"></i>
            <i class="fas fa-network-wired motion-item item-9"></i>
            <i class="fas fa-code motion-item item-10"></i>
            <i class="fas fa-lock motion-item item-11"></i>
            <i class="fas fa-wifi motion-item item-12"></i>
            <i class="fab fa-wordpress motion-item item-13"></i>
            <i class="fas fa-briefcase motion-item item-14"></i>
            <i class="fas fa-graduation-cap motion-item item-15"></i>
            <i class="fas fa-chart-line motion-item item-16"></i>
            <i class="fas fa-plug motion-item item-17"></i>
            <i class="fas fa-sitemap motion-item item-18"></i>
        </div>
        <!-- Left Side Icon Box -->
        <div class="side-icon-box left-side">
            <i class="fas fa-server"></i>
        </div>
        <!-- Right Side Icon Box -->
        <div class="side-icon-box right-side">
            <i class="fas fa-database"></i>
        </div>
        <div class="content-container">
            <div class="chooser-inner">
                <h1 class="section-headline" data-i18n="offerHero.headline">Exclusive Hosting Deals &amp; Special Offers</h1>
                <!-- Desktop Package Grid -->
                <div class="packages-desktop">
                    <div class="top-packages">
                        <a href="{{ route('page', 'web-hosting') }}" class="package-card ">
                            <span class="card-icon">⚡</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.wordpress">Web Hosting</span>
                        </a>
                        <a href="{{ route('page', 'turbo-hosting') }}" class="package-card">
                            <span class="card-icon">🚀</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.turbo">Turbo Hosting</span>
                        </a>
                        <a href="{{ route('page', 'reseller-hosting') }}" class="package-card">
                            <span class="card-icon">🔄</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.reseller">Reseller Hosting</span>
                        </a>
                        <a href="{{ route('page', 'student-hosting') }}" class="package-card">
                            <span class="card-icon">🎓</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.student">Student Hosting</span>
                        </a>
                    </div>
                    <div class="bottom-packages">
                        <a href="{{ route('page', 'vps') }}" class="package-card">
                            <span class="card-icon">🖥️</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.vps">VPS</span>
                        </a>
                        <a href="{{ route('page', 'business-email') }}" class="package-card">
                            <span class="card-icon">✉️</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.email">Business Email</span>
                        </a>
                        <a href="{{ route('page', 'offer') }}" class="package-card highlighted">
                            <span class="card-icon">🌐</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.offer">Offer</span>
                        </a>
                    </div>
                </div>
                <!-- Mobile Package Dropdown -->
                <div class="packages-mobile">
                    <div class="mobile-chooser">
                        <button type="button" class="chooser-button" id="mobileChooserBtn">
                            <span class="btn-icon" id="displayIcon">⚡</span>
                            <span class="btn-label" id="displayLabel">Web Hosting</span>
                            <i class="fas fa-chevron-down btn-chevron" id="chevronIcon"></i>
                        </button>
                        <nav class="chooser-list" id="packageList">
                            <a href="{{ route('page', 'web-hosting') }}" class="list-option selected" data-icon="⚡" data-label="web Hosting">
                                <span class="opt-icon">⚡</span>
                                <span class="opt-label" data-i18n="packageHero.wordpress">web Hosting</span>
                            </a>
                            <a href="{{ route('page', 'turbo-hosting') }}" class="list-option" data-icon="🚀" data-label="Turbo Hosting">
                                <span class="opt-icon">🚀</span>
                                <span class="opt-label" data-i18n="packageHero.turbo">Turbo Hosting</span>
                            </a>
                            <a href="{{ route('page', 'reseller-hosting') }}" class="list-option" data-icon="🔄" data-label="Reseller Hosting">
                                <span class="opt-icon">🔄</span>
                                <span class="opt-label" data-i18n="packageHero.reseller">Reseller Hosting</span>
                            </a>
                            <a href="{{ route('page', 'student-hosting') }}" class="list-option" data-icon="🎓" data-label="Student Hosting">
                                <span class="opt-icon">🎓</span>
                                <span class="opt-label" data-i18n="packageHero.student">Student Hosting</span>
                            </a>
                            <a href="{{ route('page', 'vps') }}" class="list-option" data-icon="🖥️" data-label="VPS">
                                <span class="opt-icon">🖥️</span>
                                <span class="opt-label" data-i18n="packageHero.vps">VPS</span>
                            </a>
                            <a href="{{ route('page', 'business-email') }}" class="list-option" data-icon="✉️" data-label="Business Email">
                                <span class="opt-icon">✉️</span>
                                <span class="opt-label" data-i18n="packageHero.email">Business Email</span>
                            </a>
                            <a href="{{ route('page', 'offer') }}" class="list-option" data-icon="🌐" data-label="Offer">
                                <span class="opt-icon">🌐</span>
                                <span class="opt-label" data-i18n="packageHero.">Offer</span>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- ===========================
     COUNTDOWN TIMER SECTION
     =========================== -->
<section class="offer-countdown-section">
    <div class="countdown-container">
        
        <!-- Header -->
        <div class="countdown-header">
            <div class="countdown-badge">
                <i class="fas fa-fire"></i>
                Limited Time Offer
            </div>
            <h2 class="countdown-title">
                Sale Ends In <span id="daysRemaining">7 Days</span>
            </h2>
            <p class="countdown-subtitle">Don't miss out on our exclusive hosting deals!</p>
        </div>
        <!-- Timer Display -->
        <div class="countdown-timer">
            <div class="timer-box">
                <div class="timer-value" id="days">00</div>
                <div class="timer-label">Days</div>
            </div>
            <div class="timer-separator">:</div>
            <div class="timer-box">
                <div class="timer-value" id="hours">00</div>
                <div class="timer-label">Hours</div>
            </div>
            <div class="timer-separator">:</div>
            <div class="timer-box">
                <div class="timer-value" id="minutes">00</div>
                <div class="timer-label">Minutes</div>
            </div>
            <div class="timer-separator">:</div>
            <div class="timer-box">
                <div class="timer-value" id="seconds">00</div>
                <div class="timer-label">Seconds</div>
            </div>
        </div>
        <!-- CTA Button -->
        <a href="#" class="countdown-cta">
            Grab Your Deal Now
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>
    
           <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container-wrapper">
            <!-- Section Header -->
            <div class="pricing-header">
                <h2 class="pricing-main-title" data-i18n="offerPricing.mainTitle">The Best Web Hosting</h2>
            </div>
        <!-- Cloud Pricing Section -->
        <section class="cloud-pricing-section">
        <div class="container-wrapper">
            <!-- Pricing Cards Grid -->
            <div class="cloud-pricing-cards">
                @foreach (hostorio_products('offer') as $product)
                    <x-plan-card :product="$product" page="offer" />
                @endforeach
            </div>
        </div>
        </section>
        </div>
    </section>
           <!-- advanced-features-section -->
    <section class="advanced-features-section">
        <div class="container-wrapper">
            <h2 class="section-title" data-i18n="features.sectionTitle">Enjoy all this. At no extra cost.</h2>
            <div class="features-box">
                <div class="features-columns">
                    <!-- Column 1 -->
                    <div class="feature-column">
                        <div class="feature-row">
                            <i class="fas fa-hand-holding-dollar feature-icon"></i>
                            <span class="feature-label" data-i18n="features.moneyBack"><strong>30</strong> Day Money Back Guarantee</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-server feature-icon"></i>
                            <span class="feature-label" data-i18n="features.uptime"><strong>99.9%</strong> Server Uptime Guarantee</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-clock feature-icon"></i>
                            <span class="feature-label" data-i18n="features.responseTime"><strong>1</strong> Hour Response Time</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-headset feature-icon"></i>
                            <span class="feature-label" data-i18n="features.support247"><strong>24/7</strong> Technical Support</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-network-wired feature-icon"></i>
                            <span class="feature-label" data-i18n="features.dns"><strong>Clustered</strong> Failover DNS</span>
                        </div>
                        <div class="feature-row">
                            <i class="fab fa-linux feature-icon"></i>
                            <span class="feature-label" data-i18n="features.cloudLinux"><strong>CloudLinux</strong> / CageFS</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-rocket feature-icon"></i>
                            <span class="feature-label" data-i18n="features.litespeed"><strong>LiteSpeed</strong> Web Server</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-gauge-high feature-icon"></i>
                            <span class="feature-label" data-i18n="features.cpanel"><strong>Latest</strong> cPanel</span>
                        </div>
                    </div>
                    <!-- Column 2 -->
                    <div class="feature-column">
                        <div class="feature-row">
                            <i class="fas fa-download feature-icon"></i>
                            <span class="feature-label" data-i18n="features.softaculous"><strong>Softaculous</strong> Script Installer</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-shield-virus feature-icon"></i>
                            <span class="feature-label" data-i18n="features.malware"><strong>Malware</strong> & Exploit Scanning</span>
                        </div>
                        <div class="feature-row">
                            <i class="fab fa-cloudflare feature-icon"></i>
                            <span class="feature-label" data-i18n="features.cloudflare"><strong>CloudFlare</strong> CDN</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-sitemap feature-icon"></i>
                            <span class="feature-label" data-i18n="features.subdomains"><strong>Unlimited</strong> Sub Domains</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-gem feature-icon"></i>
                            <span class="feature-label" data-i18n="features.ruby"><strong>Ruby</strong> On Rails</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-database feature-icon"></i>
                            <span class="feature-label" data-i18n="features.mysql"><strong>Unlimited</strong> MySQL Databases</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-folder-open feature-icon"></i>
                            <span class="feature-label" data-i18n="features.ftp"><strong>Unlimited</strong> FTP Accounts</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-envelope feature-icon"></i>
                            <span class="feature-label" data-i18n="features.emailAccounts"><strong>Unlimited</strong> E-Mail Accounts</span>
                        </div>
                    </div>
                    <!-- Column 3 -->
                    <div class="feature-column">
                        <div class="feature-row">
                            <i class="fas fa-shield-halved feature-icon"></i>
                            <span class="feature-label" data-i18n="features.ddos"><strong>20</strong> GBPS DDoS Protection</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-reply-all feature-icon"></i>
                            <span class="feature-label" data-i18n="features.autoResponders"><strong>Unlimited</strong> Auto Responders</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-ban feature-icon"></i>
                            <span class="feature-label" data-i18n="features.spam"><strong>Spam</strong> Protection</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-terminal feature-icon"></i>
                            <span class="feature-label" data-i18n="features.ssh"><strong>SSH</strong> Access Available</span>
                        </div>
                        <div class="feature-row">
                            <i class="fab fa-node-js feature-icon"></i>
                            <span class="feature-label" data-i18n="features.nodejs"><strong>Node.js</strong> Version Selector</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-lock feature-icon"></i>
                            <span class="feature-label" data-i18n="features.ssl"><strong>FREE</strong> Let's Encrypt SSL Certs</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-truck-fast feature-icon"></i>
                            <span class="feature-label" data-i18n="features.migration"><strong>FREE</strong> cPanel Migration</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-table feature-icon"></i>
                            <span class="feature-label" data-i18n="features.mariadb"><strong>Mariadb</strong> 10.x / phpMyAdmin</span>
                        </div>
                    </div>
                    <!-- Column 4 -->
                    <div class="feature-column">
                        <div class="feature-row">
                            <i class="fas fa-code feature-icon"></i>
                            <span class="feature-label" data-i18n="features.serverSide"><strong>Server</strong> Side Includes</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-image feature-icon"></i>
                            <span class="feature-label" data-i18n="features.curl"><strong>Curl</strong> / GD2 / ImageMagick</span>
                        </div>
                        <div class="feature-row">
                            <i class="fab fa-php feature-icon"></i>
                            <span class="feature-label" data-i18n="features.php"><strong>PHP</strong> Selector (5.6 - 8.x)</span>
                        </div>
                        <div class="feature-row">
                            <i class="fab fa-python feature-icon"></i>
                            <span class="feature-label" data-i18n="features.python"><strong>Python</strong> Version Selector</span>
                        </div>
                        <div class="feature-row">
                            <i class="fab fa-wordpress feature-icon"></i>
                            <span class="feature-label" data-i18n="features.wpManager"><strong>web</strong> Manager</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-cube feature-icon"></i>
                            <span class="feature-label" data-i18n="features.ioncube"><strong>ionCube</strong> PHP Loader</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-chart-bar feature-icon"></i>
                            <span class="feature-label" data-i18n="features.awstats"><strong>Awstats</strong> & Webalizer</span>
                        </div>
                        <div class="feature-row">
                            <i class="fas fa-z feature-icon"></i>
                            <span class="feature-label" data-i18n="features.zend"><strong>Zend</strong> Guard Loader</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!-- Web Hosting Plans Comparison Section -->
    <section class="hosting-plans-section">
        <div class="plans-container">
            <div class="plans-header">
                <h2 class="plans-title" data-i18n="comparison.title">Compare Our Web Hosting Packages</h2>
            </div>
            <div class="plans-wrapper">
                <x-plan-comparison page="offer" />
            </div>
            <div class="plans-footer">
                <p class="disclaimer">*The promotional price is for the first term only and renews at the regular rate.</p>
            </div>
        </div>
    </section>
<!-- Testimonial Carousel Section -->
<!-- Testimonial Section - Horizontal Layout -->
<section class="testimonial-horizontal-section">
    <div class="testimonial-horizontal-container">
        <!-- Testimonial Item 1 -->
        <div class="testimonial-horizontal-item active">
            <div class="testimonial-horizontal-content">
                <!-- Image Side -->
                <div class="testimonial-image-side">
                    <div class="testimonial-video-wrapper">
                        <img src="{{ asset('assets/images/testimonials/shaidulislamrony.png') }}" alt="Shaidul Islam Rony – satisfied customer who grabbed our hosting deal" class="testimonial-person-image">
                        <div class="video-play-button">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="play-svg">
                                <path fill="currentColor" d="M8 5v14l11-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Text Side -->
                <div class="testimonial-text-side">
                    <div class="quote-mark">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>
                    </div>
                    <p class="testimonial-quote" data-i18n="testimonial.item1.quote">
                        I could <span class="highlight-text">manage</span> the hosting, domain name, and SSL certificate <span class="highlight-text">in one place</span>, which was really refreshing.
                    </p>
                    <a href="#" class="testimonial-read-more" data-i18n="testimonial.readMore">Read more</a>
                    <div class="testimonial-author-section">
                        <div class="author-details">
                            <p class="author-name-text">Shaidul Islam Rony</p>
                            <p class="author-site">bhrelocationinc.com</p>
                        </div>
                        <div class="carousel-navigation">
                            <button class="carousel-btn carousel-prev" aria-label="Previous">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button class="carousel-btn carousel-next" aria-label="Next">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Testimonial Item 2 -->
        <div class="testimonial-horizontal-item">
            <div class="testimonial-horizontal-content">
                <!-- Image Side -->
                <div class="testimonial-image-side">
                    <div class="testimonial-video-wrapper">
                        <img src="{{ asset('assets/images/testimonials/tutul.png') }}" alt="Tutul – happy customer enjoying our special hosting offers" class="testimonial-person-image">
                        <div class="video-play-button">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="play-svg">
                                <path fill="currentColor" d="M8 5v14l11-7z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Text Side -->
                <div class="testimonial-text-side">
                    <div class="quote-mark">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>
                    </div>
                    <p class="testimonial-quote" data-i18n="testimonial.item2.quote">
                        We honestly reference <span class="highlight-text">Hostorio as the benchmark</span> for our engineers when providing support.
                    </p>
                    <a href="#" class="testimonial-read-more" data-i18n="testimonial.readMore">Read more</a>
                    <div class="testimonial-author-section">
                        <div class="author-details">
                            <p class="author-name-text">Tutul</p>
                            <p class="author-site">clientwebsite.com</p>
                        </div>
                        <div class="carousel-navigation">
                            <button class="carousel-btn carousel-prev" aria-label="Previous">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button class="carousel-btn carousel-next" aria-label="Next">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
       <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container-wrapper">
            <!-- Section Header -->
            <div class="faq-header">
                
                <h2 class="faq-title" data-i18n="faq.title">Frequently Asked Questions</h2>
                <p class="faq-description" data-i18n="faq.description">Find answers to common questions about our web hosting services</p>
            </div>
            <!-- FAQ Accordion -->
            <div class="faq-accordion">
                <!-- FAQ Item 1 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="faq.q1.q">How can I buy web hosting?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-i18n="faq.q1.a">It's easy – just watch the video <a href="https://youtu.be/FEFNkYCxN2M" target="_blank" class="faq-link">Youtube.com/FEFNkYCxN2M</a>, choose one of the packages, and click the buy hosting button.</p>
                    </div>
                </div>
                <!-- FAQ Item 2 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="faq.q2.q">Which web hosting plan do I need to choose?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-i18n="faq.q2.a">It depends on your needs, but most of our customers start with the Professional 2 GB & Cheap SSD Gold (5GB) web hosting as it offers them lots of flexibility at a great price!</p>
                    </div>
                </div>
                <!-- FAQ Item 3 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="faq.q3.q">Can I upgrade hosting package?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-i18n="faq.q3.a">Yes! You can upgrade your web hosting package. For example: You buy 2 GB for 1000 tk. But you want to upgrade it to a 5GB package after 6 months. You'll get a refund of 500 tk from the old package. This 500 tk will be added to the new 5GB package.</p>
                    </div>
                </div>
                <!-- FAQ Item 4 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="faq.q4.q">Can you help me to get started?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-i18n="faq.q4.a">Of course! You can join our community forum where you can find a variety of tips and tricks. <a href="#" class="faq-link">Click here</a> to join.</p>
                    </div>
                </div>
                <!-- FAQ Item 5 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="faq.q5.q">Can I transfer my website to your web hosting?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-i18n="faq.q5.a">Of course! Just buy a web hosting package that interests you and let us know that you need to get your website moved to a new server. Our technicians will do it for you free of charge.</p>
                    </div>
                </div>
                <!-- FAQ Item 6 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="faq.q6.q">Will you help me if I have any problem with hosting?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p data-i18n="faq.q6.a">Of course! We always make sure our customers don't have any problems. We will try our best. We are committed to your service. Feel free to connect with us.</p>
                    </div>
                </div>
            </div>
            <!-- Contact CTA -->
            <div class="faq-cta">
                <div class="faq-cta-content">
                    <i class="fas fa-comments faq-cta-icon"></i>
                    <div class="faq-cta-text">
                        <h3 data-i18n="faq.cta.title">Still have questions?</h3>
                        <p data-i18n="faq.cta.desc">Can't find the answer you're looking for? Please chat with our friendly team.</p>
                    </div>
                </div>
                <a href="https://my.hostorio.com/submitticket.php" class="faq-cta-button" data-i18n="faq.cta.btn">Get in Touch</a>
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
                        <img src="{{ asset('assets/images/support/support-hostorio.webp') }}" alt="Hostorio 24/7 support included free with all hosting deals and special offers" class="card-image">
                        <div class="image-overlay blue-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title" data-i18n="support.card1.title">Onboarding Support</h3>
                        <p class="card-description" data-i18n="support.card1.desc">
                            Don't know where to start? Our step-by-step tutorials and 24/7 support team walk you through domain setup, hosting configuration, and WordPress installation.
                        </p>
                        <a href="https://my.hostorio.com/submitticket.php" class="card-button" data-i18n="support.card1.btn">Get started</a>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="support-card">
                    <div class="card-image-wrapper">
                        <img src="{{ asset('assets/images/support/blog-hostorio.webp') }}" alt="Free tutorials and guides included with every hosting deal – maximize your investment" class="card-image">
                        <div class="image-overlay green-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title" data-i18n="support.card2.title">Discover our Resources</h3>
                        <p class="card-description" data-i18n="support.card2.desc">
                            Access our resource center for hosting best practices, security tips, performance optimization guides, and industry insights.
                        </p>
                        <a href="https://my.hostorio.com/knowledgebase" class="card-button" data-i18n="support.card2.btn">Get learning</a>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="support-card">
                    <div class="card-image-wrapper">
                        <img src="{{ asset('assets/images/support/trasfer-hosting-hostorio.webp') }}" alt="Free website migration included with all special hosting offers – switch risk-free" class="card-image">
                        <div class="image-overlay orange-overlay"></div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title" data-i18n="support.card3.title">Hassle-Free Transfers</h3>
                        <p class="card-description" data-i18n="support.card3.desc">
                            Move your domain and Hosting to Us. Switching hosts shouldn't feel like moving houses. With us, it's simple.
                        </p>
                        <a href="https://my.hostorio.com/cart.php?a=add&domain=transfer" class="card-button" data-i18n="support.card3.btn">Transfer today</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Main Footer -->
    
@endsection
