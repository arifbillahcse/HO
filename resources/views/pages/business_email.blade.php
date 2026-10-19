@extends('layouts.app')
@push('head')
<style>
</style>
@endpush
@push('translations')
<script src="{{ asset('assets/js/translations/header-footer/header.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/hero.js') }}"></script>
<script src="{{ asset('assets/js/translations/business-email/pricing.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/testimonial.js') }}"></script>
<script src="{{ asset('assets/js/translations/Package/support.js') }}"></script>
<script src="{{ asset('assets/js/translations/header-footer/footer.js') }}"></script>
@endpush
@section('content')
@include('partials.schema.organization')
    @php($serviceRange = app(\App\Services\ProductCatalog::class)->priceRange('business_email'))
    <script type="application/ld+json">
{
      "@@context": "https://schema.org",
      "@type": "Service",
      "serviceType": "Business Email Hosting",
      "name": "Professional Business Email Hosting",
      "description": "Professional business email hosting with custom domain addresses. Includes spam protection, webmail interface, mobile synchronization, and 24/7 support. Perfect for small businesses, professionals, and companies looking to build brand credibility with custom email addresses.",
      "provider": {
        "@type": "Organization",
        "name": @json(hostorio_business()->name()),
        "url": "https://hostorio.com"
      },
      "areaServed": {
        "@type": "Country",
        "name": "Bangladesh"
      },
      "offers": {
        "@type": "AggregateOffer",
        "url": "{{ hostorio_url('business-email') }}",
        "priceCurrency": "BDT",
        "lowPrice": {{ $serviceRange['low'] }},
        "highPrice": {{ $serviceRange['high'] }},
        "offerCount": {{ $serviceRange['count'] }},
        "availability": "https://schema.org/InStock"
      }
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
          "name": "Services",
          "item": "https://hostorio.com/#services"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Business Email",
          "item": "{{ hostorio_url('business-email') }}"
        }
      ]
    }
    </script>
    <script type="application/ld+json">
{
      "@@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "What is email hosting?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Email hosting is server space that stores your mailbox and manages the sending and receiving of emails. It connects to your domain allowing you to create professional email addresses (yourname@yourdomain.com). Our business email hosting includes storage, delivery service, integrations, and collaboration features designed to boost productivity."
          }
        },
        {
          "@type": "Question",
          "name": "Can I find out who is hosting my email right now?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes. An email hosting provider is defined by MX records that point incoming emails to a particular provider. You can perform an MX lookup using free online services such as MXtoolbox to find your current email hosting provider by looking up the MX records for your domain."
          }
        },
        {
          "@type": "Question",
          "name": "How do I get a company email address?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "First, register a domain name. If your domain is with Hostorio and you purchase an email hosting plan, all records will be set up automatically. For third-party domains, you can still get a professional email plan with us and set it up in a few clicks. Once your email plan is set up, yourname@yourdomain.com is ready to go!"
          }
        },
        {
          "@type": "Question",
          "name": "What are the benefits of email hosting?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "With our hosted email service, you rent high-quality email servers to manage your business communications. Our email hosting is a premium solution that gives you more flexibility, power, and features compared to free email options, along with 24/7 customer support."
          }
        },
        {
          "@type": "Question",
          "name": "What are the best features of our Email Hosting?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Our email hosting is collaborative with a fresh interface that works across all devices (tablets, desktops, mobile, notebooks). It allows shared folders, work schedules management, task control, and meeting planning. Perfect for businesses looking to create a professional email presence that builds trust with customers."
          }
        },
        {
          "@type": "Question",
          "name": "Is it necessary to have a web hosting plan to use Email Hosting?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "No, you don't need any web hosting plan to use our Email Hosting. They are two separate products and can be bought independently, but they also work smoothly together."
          }
        },
        {
          "@type": "Question",
          "name": "Can I see what the product interface looks like before buying it?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes, we have plenty of videos that show the look and feel of our Email Hosting interface. You can explore how to set up an email account, migrate email, manage your business email address, and much more."
          }
        },
        {
          "@type": "Question",
          "name": "Are email migrations available when switching to our Email Hosting?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes. If you are using a third-party email service and would like to switch to our Email Hosting, you can easily migrate your old emails to our servers using the webmail interface. We support importing emails from Outlook, Thunderbird, MacMail, and other web-based email services seamlessly."
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
                <h1 class="section-headline" data-i18n="emailHero.headline">Professional Business Email Solutions</h1>
                <!-- Desktop Package Grid -->
                <div class="packages-desktop">
                    <div class="top-packages">
                        <a href="{{ hostorio_url('web-hosting') }}" class="package-card">
                            <span class="card-icon">⚡</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.wordpress">Web Hosting</span>
                        </a>
                        <a href="{{ hostorio_url('turbo-hosting') }}" class="package-card">
                            <span class="card-icon">🚀</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.turbo">Turbo Hosting</span>
                        </a>
                        <a href="{{ hostorio_url('reseller-hosting') }}" class="package-card">
                            <span class="card-icon">🔄</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.reseller">Reseller Hosting</span>
                        </a>
                        <a href="{{ hostorio_url('student-hosting') }}" class="package-card">
                            <span class="card-icon">🎓</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.student">Student Hosting</span>
                        </a>
                    </div>
                    <div class="bottom-packages">
                        <a href="{{ hostorio_url('vps') }}" class="package-card">
                            <span class="card-icon">🖥️</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.vps">VPS</span>
                        </a>
                        <a href="{{ hostorio_url('business-email') }}" class="package-card highlighted">
                            <span class="card-icon">✉️</span>
                            <span class="card-title-pricing-hero" data-i18n="packageHero.email">Business Email</span>
                        </a>
                        <a href="{{ hostorio_url('offer') }}" class="package-card">
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
                            <a href="{{ hostorio_url('web-hosting') }}" class="list-option selected" data-icon="⚡" data-label="Web Hosting">
                                <span class="opt-icon">⚡</span>
                                <span class="opt-label" data-i18n="packageHero.wordpress">Web Hosting</span>
                            </a>
                            <a href="{{ hostorio_url('turbo-hosting') }}" class="list-option" data-icon="🚀" data-label="Turbo Hosting">
                                <span class="opt-icon">🚀</span>
                                <span class="opt-label" data-i18n="packageHero.turbo">Turbo Hosting</span>
                            </a>
                            <a href="{{ hostorio_url('reseller-hosting') }}" class="list-option" data-icon="🔄" data-label="Reseller Hosting">
                                <span class="opt-icon">🔄</span>
                                <span class="opt-label" data-i18n="packageHero.reseller">Reseller Hosting</span>
                            </a>
                            <a href="{{ hostorio_url('student-hosting') }}" class="list-option" data-icon="🎓" data-label="Student Hosting">
                                <span class="opt-icon">🎓</span>
                                <span class="opt-label" data-i18n="packageHero.student">Student Hosting</span>
                            </a>
                            <a href="{{ hostorio_url('vps') }}" class="list-option" data-icon="🖥️" data-label="VPS">
                                <span class="opt-icon">🖥️</span>
                                <span class="opt-label" data-i18n="packageHero.vps">VPS</span>
                            </a>
                            <a href="{{ hostorio_url('business-email') }}" class="list-option" data-icon="✉️" data-label="Business Email">
                                <span class="opt-icon">✉️</span>
                                <span class="opt-label" data-i18n="packageHero.email">Business Email</span>
                            </a>
                            <a href="{{ hostorio_url('offer') }}" class="list-option" data-icon="🌐" data-label="Domain">
                                <span class="opt-icon">🌐</span>
                                <span class="opt-label" data-i18n="packageHero.offer">Offer</span>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
           <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container-wrapper">
            <!-- Section Header -->
            <div class="pricing-header">
                <h2 class="pricing-main-title" data-i18n="emailPricing.mainTitle">Choose Your Business Email Plan</h2>
                <p class="pricing-subtitle" data-i18n="emailPricing.subtitle">Professional email hosting to keep your business communication secure and reliable.</p>
                <!-- Billing Toggle -->
                <div class="billing-toggle">
                    <span class="toggle-label" data-i18n="emailPricing.billedMonthly">Billed Monthly</span>
                    <label class="toggle-switch">
                        <input type="checkbox" id="billingToggle" checked>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-label active" data-i18n="emailPricing.billedAnnually">Billed Annually</span>
                    <span class="save-badge" data-i18n="emailPricing.saveBadge">Save 30%!</span>
                </div>
            </div>
        <!-- Cloud Pricing Section -->
        <section class="cloud-pricing-section">
        <div class="container-wrapper">
            <!-- Pricing Cards Grid -->
            <div class="cloud-pricing-cards" style="justify-content: center;">
                @foreach (hostorio_products('business_email') as $product)
                    <x-plan-card :product="$product" page="business_email" />
                @endforeach
            </div>
        </div>
        </section>
        </div>
    </section>
   <!-- Jellyfish Section -->
    <section class="jellyfish-section">
        <div class="container-wrapper">
            
            <!-- Section Header -->
            <div class="jellyfish-header">
                <h2 class="jellyfish-main-title" data-i18n="emailJellyfish.mainTitle">Keep spam out of your Inbox with Jellyfish</h2>
            </div>
            <!-- Content Area -->
            <div class="jellyfish-content">
                <!-- Right Side - Image -->
                <div class="jellyfish-image-wrapper">
                    <img src="https://static.nc-img.com/uiprivateemailcms/16895731e887a21d77c41442f39ec90d.png" alt="Keep spam out of your Inbox with Jellyfish" class="jellyfish-image">
                </div>
                <!-- Left Side - Text Content -->
                <div class="jellyfish-text">
                    <div class="jellyfish-title-wrapper">
                        <h3 class="jellyfish-subtitle" data-i18n="emailJellyfish.subtitle">Robust spam control</h3>
                        <span class="jellyfish-badge" data-i18n="emailJellyfish.badge">New anti-spam tool</span>
                    </div>
                    <div class="jellyfish-description">
                        <p data-i18n="emailJellyfish.desc1">See spam disappear over time as Jellyfish uses machine learning to tighten the net on junk emails — based on your preferences.</p>
                        <p data-i18n="emailJellyfish.desc2">From tailoring your email flow to creating specific advanced filter rules, Jellyfish is ready! Its smart machine-learning technology works quietly in the background to ensure your inbox is always free of spam.</p>
                    </div>
                    <a href="#" class="jellyfish-link">
                        <span data-i18n="emailJellyfish.link">Dive Deeper into Jellyfish</span>
                        <svg class="arrow-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <div class="jellyfish-features">
                        <div class="jellyfish-features-col">
                            <ul class="jellyfish-features-list">
                                <li>
                                    <svg class="feature-check" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <circle cx="10" cy="10" r="10" fill="#FF7800"/>
                                        <path d="M6 10L9 13L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span data-i18n="emailJellyfish.features.intelligentFilter">Intelligent anti-spam filter</span>
                                </li>
                                <li>
                                    <svg class="feature-check" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <circle cx="10" cy="10" r="10" fill="#FF7800"/>
                                        <path d="M6 10L9 13L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span data-i18n="emailJellyfish.features.machineLearning">Smart machine-learning algorithms</span>
                                </li>
                            </ul>
                        </div>
                        <div class="jellyfish-features-col">
                            <ul class="jellyfish-features-list">
                                <li>
                                    <svg class="feature-check" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <circle cx="10" cy="10" r="10" fill="#FF7800"/>
                                        <path d="M6 10L9 13L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span data-i18n="emailJellyfish.features.adminPanel">Dedicated admin panel for filter rules and email logs</span>
                                </li>
                                <li>
                                    <svg class="feature-check" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                        <circle cx="10" cy="10" r="10" fill="#FF7800"/>
                                        <path d="M6 10L9 13L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span data-i18n="emailJellyfish.features.noConfig">No configuration needed</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
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
                        <img src="{{ asset('assets/images/testimonials/shaidulislamrony.png') }}" alt="Shaidul Islam Rony – business email customer review" class="testimonial-person-image">
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
                        <img src="{{ asset('assets/images/testimonials/tutul.png') }}" alt="Tutul – satisfied business email customer" class="testimonial-person-image">
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
                <span class="faq-tag" data-i18n="emailFaq.tag">FAQ</span>
                <h2 class="faq-title" data-i18n="emailFaq.title">Frequently Asked Questions</h2>
                <p class="faq-description" data-i18n="emailFaq.description">Find answers to common questions about our email hosting services</p>
            </div>
            <!-- FAQ Accordion -->
            <div class="faq-accordion">
                <!-- FAQ Item 1 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q1">What is email hosting?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Just like web hosting, email hosting is server space that stores your mailbox rather than a website. It also has a portal that manages the sending and receiving of emails. Like web hosting, it connects to your domain allowing you to make professional email addresses (<strong>yourname@yourdomain.com</strong>). Most companies basically rent out space on an email server to customers, then handle email sending and receiving.</p>
                        <p>Our professional business email is, however, more than just storage space and a delivery service. It also includes a wide variety of integrations and collaboration features designed to boost productivity and team effectiveness.</p>
                        <a href="#" class="faq-link">Discover more about our email hosting</a>
                    </div>
                </div>
                <!-- FAQ Item 2 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q2">Can I find out who is hosting my email right now?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>An email hosting provider is usually defined by MX records that point incoming emails to a particular provider — who then processes those messages. To find your MX records, you'll need to perform an MX lookup. For example, if your email address is <strong>yourname@yourdomain.com</strong> you need to look up MX records for yourdomain.com.</p>
                        <p>Free online services such as <a href="https://mxtoolbox.com/" target="_blank" class="faq-link">MXtoolbox</a> will give you a list of MX records that usually offer a provider's name and corresponding IP address records. Sometimes, though, a domain might have a custom domain-based email MX record (e.g. mail.yourdomain.com). If that's the case, you should also perform a Whois search on one of the IP addresses. That will give you the name of the email provider.</p>
                    </div>
                </div>
                <!-- FAQ Item 3 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q3">How do I get a company email address?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>That's easy!</p>
                        <p>To get a custom email, first, you need to register a domain name. You can do this with any domain name registrar. But, if your domain is with us and you purchase an email hosting plan, all your records will be set up automatically in most cases.</p>
                        <p>If you registered your domain name somewhere else, it's called a third-party domain name. But don't worry. You can still get a professional email plan with us, and easily set this up in a few clicks. We'll email you the steps to set up your account when you purchase your email hosting.</p>
                        <p>Once you have an email plan set up, yourname@yourdomain.com is ready to go!</p>
                    </div>
                </div>
                <!-- FAQ Item 4 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q4">What are the benefits of email hosting?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>With our hosted email service, you are renting high-quality email servers to manage the communications of your business. Some hosting providers offer free email hosting options with very few features and little to no support. Our email hosting is a premium email solution that gives you more flexibility and power. It's just one of the reasons why so many business owners choose us, along with our 24/7 customer support.</p>
                    </div>
                </div>
                <!-- FAQ Item 5 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q5">What are the best features of our Email Hosting?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Our email hosting is collaborative and provides a fresh interface that works across all devices — including tablets, desktops, mobile, and notebooks – allowing users to communicate whenever, and wherever, they want. It allows users to add a public space using shared folders, create and manage work schedules, and much more. Users can set and control tasks with ease. It's also easy to see the availability of colleagues when planning meetings and events.</p>
                        <p>Our email hosting is perfect for big and small businesses looking to create a professional email presence. It enables users to set up custom business email addresses that build trust with customers.</p>
                    </div>
                </div>
                <!-- FAQ Item 6 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q6">Is it necessary to have a web hosting plan to use Email Hosting?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>No, you don't need any web hosting plan to use our Email Hosting. They are two separate products and can be bought independently of one another, but they also work smoothly together.</p>
                    </div>
                </div>
                <!-- FAQ Item 7 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q7">Can I see what the product interface looks like before buying it?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes — we have plenty of videos that show the look and feel of our Email Hosting interface. You can explore how to set up an email account, migrate email, manage your business email address, and much more.</p>
                    </div>
                </div>
                <!-- FAQ Item 8 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q8">Are email migrations available when switching to our Email Hosting?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>If you are using a third-party email service hosted elsewhere and would like to switch to our Email Hosting, you can easily migrate your old emails to our servers using the webmail interface. Important messages from your Inbox, Drafts, Sent, and other folders can all be moved to your new email accounts. Whether you are using Outlook, Thunderbird, MacMail, or another web-based email service, we assure you that our email migration service supports importing emails and setting up your business email addresses seamlessly.</p>
                    </div>
                </div>
                <!-- FAQ Item 9 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q9">Which 2FA methods do you support?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Our Email Hosting supports TOTP methods of two-factor authentication which is one of the stronger 2FA methods.</p>
                        <p>TOTP (Time-based One-Time Password) is a standardized method that generates a changing temporary code that is unique each time and expires as soon as the new one has been generated. Implementing 2FA enhances the security of your business email account, ensuring that your encrypted email communication remains protected with an additional layer of security.</p>
                    </div>
                </div>
                <!-- FAQ Item 10 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q10">How can I access my email account?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>If you have already created an email account with us, you can access it by visiting our email login page. Enter your full email address (e.g. <strong>yourname@yourbusiness.com</strong>) and your password, then click the "Login" button to access your inbox.</p>
                        <p>For those who haven't created an email account yet, start by setting up your email account. This involves creating an email address using your custom domain. For instance, if your domain name is <strong>yourbusiness.com</strong>, you might create a professional email address such as <strong>yourname@yourbusiness.com</strong>.</p>
                    </div>
                </div>
                <!-- FAQ Item 11 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q11">How can I set up my Email Hosting subscription?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>After selecting your Email Hosting plan and registering your domain name, you can start setting up an email account to meet your business needs. This process usually involves configuring the correct DNS records and activating your mailbox. Following our easy step-by-step guides will help you establish a custom business email address, which builds trust with your clients and enhances your professional image. Our 24/7 customer support is available to answer any questions you might have.</p>
                    </div>
                </div>
                <!-- FAQ Item 12 -->
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        <div class="faq-question-content">
                            <i class="fas fa-circle-question question-icon"></i>
                            <h3 class="faq-question-text" data-i18n="emailFaq.q12">Can I use Outlook/Gmail with my email addresses?</h3>
                        </div>
                        <i class="fas fa-chevron-down faq-arrow"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, you can use Outlook or Gmail with your email address. This flexibility allows you to access your business email through your preferred email client, enhancing your professional email experience.</p>
                        <p>To set up your email account with Outlook or Gmail, you need to configure it using IMAP/POP3/SMTP settings. Detailed guidelines for various operating systems and email clients can be found in our knowledge base.</p>
                        <p>These guides will help you set up your email account correctly, ensuring secure email access and communication. Our 24/7 customer support is also ready to answer any questions.</p>
                    </div>
                </div>
            </div>
            <!-- Contact CTA -->
            <div class="faq-cta">
                <div class="faq-cta-content">
                    <i class="fas fa-comments faq-cta-icon"></i>
                    <div class="faq-cta-text">
                        <h3 data-i18n="emailFaq.ctaTitle">Still have questions?</h3>
                        <p data-i18n="emailFaq.ctaDesc">Can't find the answer you're looking for? Please chat with our friendly team.</p>
                    </div>
                </div>
                <a href="#" class="faq-cta-button" data-i18n="emailFaq.ctaBtn">Get in Touch</a>
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
                    <img src="{{ asset('assets/images/support/support-hostorio.webp') }}" alt="Onboarding Support" class="card-image">
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
                    <img src="{{ asset('assets/images/support/blog-hostorio.webp') }}" alt="Discover Resources" class="card-image">
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
                    <img src="{{ asset('assets/images/support/trasfer-hosting-hostorio.webp') }}" alt="Hassle-Free Transfers" class="card-image">
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
    <!-- Main Footer -->
    
@endsection
