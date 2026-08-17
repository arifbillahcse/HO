<?php
/**
 * Template Name: About Us
 * Description: About Us page template for Hostorio theme.
 *
 * @package Hostorio
 */

// Register SEO meta and schema markup before get_header() fires wp_head
add_action( 'wp_head', function() {
    if ( ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'RankMath' ) ) {
        echo '<meta name="description" content="Learn about Hostorio – a Bangladesh-based web hosting provider delivering LiteSpeed-powered NVMe hosting, domains, VPS &amp; business email with 24/7 bilingual support.">' . "\n";
        echo '<link rel="canonical" href="https://hostorio.com/about-us/">' . "\n";
        echo '<meta name="robots" content="index, follow">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:title" content="About Hostorio – Web Hosting Built for Bangladesh">' . "\n";
        echo '<meta property="og:description" content="Hostorio provides fast, secure and affordable web hosting in Bangladesh. LiteSpeed servers, NVMe storage, free SSL and 24/7 support in English &amp; Bengali.">' . "\n";
        echo '<meta property="og:image" content="https://hostorio.com/assets/images/logo/hostorio-logo.png">' . "\n";
        echo '<meta property="og:site_name" content="Hostorio">' . "\n";
        echo '<meta property="og:locale" content="en_US">' . "\n";
        echo '<meta property="og:url" content="https://hostorio.com/about-us/">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:site" content="@hostorio">' . "\n";
        echo '<meta name="twitter:title" content="About Hostorio – Web Hosting Built for Bangladesh">' . "\n";
        echo '<meta name="twitter:description" content="Fast, secure and affordable web hosting in Bangladesh. LiteSpeed servers, NVMe storage, free SSL and 24/7 bilingual support.">' . "\n";
        echo '<meta name="twitter:image" content="https://hostorio.com/assets/images/logo/hostorio-logo.png">' . "\n";
    }
    ?>
    <script type="application/ld+json">
{
      "@context": "https://schema.org",
      "@type": "AboutPage",
      "name": "About Hostorio",
      "url": "https://hostorio.com/about-us/",
      "description": "Hostorio is a web hosting services provider in Bangladesh offering shared hosting, turbo hosting, reseller hosting, VPS, domain registration, and business email solutions.",
      "mainEntity": {
        "@type": "Organization",
        "name": "Hostorio",
        "url": "https://hostorio.com",
        "logo": "https://hostorio.com/assets/images/logo/hostorio-logo.png",
        "description": "Hostorio is a web hosting services provider in Bangladesh offering shared hosting, turbo hosting, reseller hosting, VPS, domain registration, and business email solutions.",
        "contactPoint": {
          "@type": "ContactPoint",
          "telephone": "+8801779440297",
          "contactType": "customer service",
          "email": "mail@hostorio.com",
          "areaServed": "BD",
          "availableLanguage": ["English", "Bengali"]
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
    }
    </script>
    <script type="application/ld+json">
{
      "@context": "https://schema.org",
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
          "name": "About Us",
          "item": "https://hostorio.com/about-us/"
        }
      ]
    }
    </script>
    <?php
}, 1 );

get_header();
?>
<style>

    /* =============================================
       ABOUT US PAGE — PAGE-SPECIFIC CSS
       ============================================= */

    /* ── Hero Section ───────────────────────────── */
    .about-hero-section {
        background: linear-gradient(135deg, #01257D 0%, #0C379A 55%, #01206b 100%);
        padding: 100px 20px 70px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .about-hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(ellipse at center, rgba(255, 120, 0, 0.12) 0%, transparent 60%);
        pointer-events: none;
    }

    .about-hero-inner {
        position: relative;
        z-index: 1;
        max-width: 820px;
        margin: 0 auto;
    }

    .about-hero-icon {
        font-size: 3rem;
        color: #FF9000;
        margin-bottom: 18px;
        display: inline-block;
    }

    .about-hero-section h1 {
        font-size: 2.8rem;
        font-weight: 800;
        color: #ffffff;
        margin: 0 0 16px;
        line-height: 1.2;
    }

    .about-hero-section p {
        font-size: 1.1rem;
        color: #c3d6f2;
        max-width: 680px;
        margin: 0 auto;
        line-height: 1.7;
    }

    /* ── Stats Bar ──────────────────────────────── */
    .about-stats-section {
        background: #ffffff;
        padding: 0 20px;
        position: relative;
        z-index: 2;
    }

    .about-stats-inner {
        max-width: 1000px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 8px 30px rgba(1, 37, 125, 0.12);
        padding: 34px 30px;
        margin-top: -45px;
    }

    .about-stat-item {
        text-align: center;
        padding: 6px 10px;
        border-right: 1px solid #e8eef5;
    }

    .about-stat-item:last-child {
        border-right: none;
    }

    .about-stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: #0C379A;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .about-stat-label {
        font-size: 0.85rem;
        color: #808285;
        font-weight: 500;
    }

    /* ── Generic Content Section ────────────────── */
    .about-content-section {
        background: #f4f7fb;
        padding: 70px 20px 80px;
    }

    .about-content-inner {
        max-width: 1000px;
        margin: 0 auto;
    }

    .about-section-heading {
        text-align: center;
        margin-bottom: 40px;
    }

    .about-section-heading h2 {
        font-size: 1.9rem;
        font-weight: 800;
        color: #01257D;
        margin: 0 0 12px;
        line-height: 1.3;
    }

    .about-section-heading p {
        font-size: 1rem;
        color: #5a6a7e;
        max-width: 640px;
        margin: 0 auto;
        line-height: 1.75;
    }

    /* ── Story Box ──────────────────────────────── */
    .about-story-box {
        background: #ffffff;
        border-left: 5px solid #FF7800;
        border-radius: 8px;
        padding: 30px 34px;
        margin-bottom: 44px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .about-story-label {
        font-size: 0.85rem;
        font-weight: 700;
        color: #FF7800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 12px;
    }

    .about-story-box p {
        font-size: 1rem;
        color: #4a5568;
        line-height: 1.85;
        margin: 0 0 16px;
    }

    .about-story-box p:last-child {
        margin-bottom: 0;
    }

    /* ── Mission / Vision Grid ──────────────────── */
    .about-mv-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        margin-bottom: 20px;
    }

    .about-mv-card {
        background: #ffffff;
        border-radius: 10px;
        padding: 30px 32px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1px solid #e8eef5;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }

    .about-mv-card:hover {
        box-shadow: 0 8px 24px rgba(1, 37, 125, 0.12);
        transform: translateY(-3px);
    }

    .about-mv-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #ffffff;
        margin-bottom: 18px;
        background: linear-gradient(135deg, #0C379A 0%, #1368E7 100%);
    }

    .about-mv-card.accent .about-mv-icon {
        background: linear-gradient(135deg, #FF7800 0%, #FF9000 100%);
    }

    .about-mv-card h3 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #01257D;
        margin: 0 0 12px;
    }

    .about-mv-card p {
        font-size: 0.95rem;
        color: #5a6a7e;
        line-height: 1.8;
        margin: 0;
    }

    /* ── Services Grid ──────────────────────────── */
    .about-services-section {
        background: #ffffff;
        padding: 70px 20px 80px;
    }

    .about-services-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 22px;
    }

    .about-service-card {
        display: block;
        background: #ffffff;
        border: 1px solid #e8eef5;
        border-radius: 10px;
        padding: 26px 24px;
        text-decoration: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: box-shadow 0.25s ease, border-color 0.25s ease, transform 0.25s ease;
    }

    .about-service-card:hover {
        box-shadow: 0 8px 22px rgba(1, 37, 125, 0.12);
        border-color: #cce0ff;
        transform: translateY(-3px);
    }

    .about-service-emoji {
        font-size: 1.9rem;
        display: block;
        margin-bottom: 14px;
    }

    .about-service-card h3 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #01257D;
        margin: 0 0 10px;
    }

    .about-service-card p {
        font-size: 0.9rem;
        color: #5a6a7e;
        line-height: 1.75;
        margin: 0 0 14px;
    }

    .about-service-link {
        font-size: 0.85rem;
        font-weight: 700;
        color: #FF7800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* ── Why Choose Us ──────────────────────────── */
    .about-why-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }

    .about-why-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 24px 26px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border: 1px solid #e8eef5;
        display: flex;
        gap: 16px;
        align-items: flex-start;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .about-why-card:hover {
        box-shadow: 0 4px 18px rgba(1, 37, 125, 0.1);
        border-color: #cce0ff;
    }

    .about-why-card i {
        color: #FF7800;
        font-size: 1.15rem;
        margin-top: 3px;
        flex-shrink: 0;
    }

    .about-why-card h3 {
        font-size: 1rem;
        font-weight: 700;
        color: #01257D;
        margin: 0 0 8px;
    }

    .about-why-card p {
        font-size: 0.92rem;
        color: #5a6a7e;
        line-height: 1.75;
        margin: 0;
    }

    /* ── Technology Strip ───────────────────────── */
    .about-tech-section {
        background: linear-gradient(135deg, #01257D 0%, #0C379A 100%);
        padding: 64px 20px 70px;
    }

    .about-tech-inner {
        max-width: 1000px;
        margin: 0 auto;
    }

    .about-tech-inner .about-section-heading h2 {
        color: #ffffff;
    }

    .about-tech-inner .about-section-heading p {
        color: #c3d6f2;
    }

    .about-tech-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    .about-tech-item {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        padding: 24px 18px;
        text-align: center;
    }

    .about-tech-item i {
        font-size: 1.6rem;
        color: #FF9000;
        margin-bottom: 12px;
        display: block;
    }

    .about-tech-item h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 6px;
    }

    .about-tech-item p {
        font-size: 0.82rem;
        color: #a8c0d8;
        line-height: 1.6;
        margin: 0;
    }

    /* ── Contact / CTA Box ──────────────────────── */
    .about-contact-section {
        background: #f4f7fb;
        padding: 70px 20px 80px;
    }

    .about-contact-box {
        max-width: 1000px;
        margin: 0 auto;
        background: linear-gradient(135deg, #01257D 0%, #0C379A 100%);
        border-radius: 12px;
        padding: 40px 44px;
        color: #ffffff;
    }

    .about-contact-box h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #ffffff;
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .about-contact-box h2 i {
        color: #FF9000;
    }

    .about-contact-box > p {
        font-size: 0.98rem;
        color: #c3d6f2;
        line-height: 1.8;
        margin: 0 0 26px;
        max-width: 700px;
    }

    .about-contact-list {
        list-style: none;
        padding: 0;
        margin: 0 0 26px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .about-contact-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 0.93rem;
        color: #dce8ff;
        line-height: 1.7;
    }

    .about-contact-list li i {
        color: #FF9000;
        margin-top: 4px;
        flex-shrink: 0;
    }

    .about-contact-list a {
        color: #ffffff;
        text-decoration: underline;
    }

    .about-contact-list a:hover {
        color: #FF9000;
    }

    .about-contact-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .about-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 28px;
        border-radius: 50px;
        font-size: 0.93rem;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .about-btn-primary {
        background: linear-gradient(135deg, #FF7800 0%, #FF9000 100%);
        color: #ffffff;
    }

    .about-btn-primary:hover {
        transform: translateY(-2px);
        color: #ffffff;
    }

    .about-btn-outline {
        background: transparent;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.45);
    }

    .about-btn-outline:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
    }

    .about-contact-note {
        font-size: 0.85rem;
        color: #a8c0d8;
        margin: 22px 0 0;
    }

    /* ── Responsive ─────────────────────────────── */
    @media (max-width: 900px) {
        .about-stats-inner {
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .about-stat-item:nth-child(2) {
            border-right: none;
        }

        .about-services-grid,
        .about-tech-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .about-hero-section {
            padding: 70px 20px 60px;
        }

        .about-hero-section h1 {
            font-size: 2rem;
        }

        .about-hero-section p {
            font-size: 1rem;
        }

        .about-section-heading h2 {
            font-size: 1.5rem;
        }

        .about-mv-grid,
        .about-why-grid,
        .about-contact-list {
            grid-template-columns: 1fr;
        }

        .about-story-box,
        .about-mv-card {
            padding: 24px 22px;
        }

        .about-contact-box {
            padding: 30px 24px;
        }

        .about-contact-box h2 {
            font-size: 1.3rem;
        }
    }

    @media (max-width: 480px) {
        .about-hero-section h1 {
            font-size: 1.6rem;
        }

        .about-hero-icon {
            font-size: 2.2rem;
        }

        .about-stats-inner {
            grid-template-columns: 1fr;
            padding: 26px 20px;
        }

        .about-stat-item {
            border-right: none;
            border-bottom: 1px solid #e8eef5;
            padding-bottom: 18px;
        }

        .about-stat-item:last-child {
            border-bottom: none;
            padding-bottom: 6px;
        }

        .about-services-grid,
        .about-tech-grid {
            grid-template-columns: 1fr;
        }

        .about-btn {
            width: 100%;
            justify-content: center;
        }
    }

</style>

<!-- ===========================
     HERO SECTION
     =========================== -->
<section class="about-hero-section">
    <div class="about-hero-inner">
        <i class="fas fa-building about-hero-icon"></i>
        <h1 data-i18n="about.hero.title">About Hostorio</h1>
        <p data-i18n="about.hero.subtitle">We are a Bangladesh-based web hosting company on a simple mission: make fast, secure and genuinely affordable hosting available to every business, student and creator in the country.</p>
    </div>
</section>

<!-- ===========================
     STATS BAR
     =========================== -->
<section class="about-stats-section">
    <div class="about-stats-inner">
        <div class="about-stat-item">
            <div class="about-stat-value" data-i18n="about.stats.uptime.value">99.9%</div>
            <div class="about-stat-label" data-i18n="about.stats.uptime.label">Uptime Guarantee</div>
        </div>
        <div class="about-stat-item">
            <div class="about-stat-value" data-i18n="about.stats.support.value">24/7</div>
            <div class="about-stat-label" data-i18n="about.stats.support.label">Expert Support</div>
        </div>
        <div class="about-stat-item">
            <div class="about-stat-value" data-i18n="about.stats.price.value">৳999</div>
            <div class="about-stat-label" data-i18n="about.stats.price.label">Hosting From / Year</div>
        </div>
        <div class="about-stat-item">
            <div class="about-stat-value" data-i18n="about.stats.guarantee.value">30 Days</div>
            <div class="about-stat-label" data-i18n="about.stats.guarantee.label">Money-Back Guarantee</div>
        </div>
    </div>
</section>

<!-- ===========================
     OUR STORY + MISSION / VISION
     =========================== -->
<section class="about-content-section">
    <div class="about-content-inner">

        <!-- Story -->
        <div class="about-story-box">
            <div class="about-story-label" data-i18n="about.story.label">Who We Are</div>
            <p data-i18n="about.story.p1">Hostorio is a web hosting services provider based in Narayanganj, Bangladesh. We provide shared hosting, turbo hosting, reseller hosting, student hosting, VPS, domain registration and business email — everything a website needs to go live and stay online, under one roof and one bill.</p>
            <p data-i18n="about.story.p2">We started Hostorio because getting a website online in Bangladesh was harder and more expensive than it needed to be. Local businesses were paying overseas prices for slow servers, waiting days for support replies, and dealing with English-only help desks. We built the alternative: LiteSpeed-powered NVMe hosting, transparent pricing in Taka, and a support team that answers in Bangla or English — whichever you prefer.</p>
            <p data-i18n="about.story.p3">Today we host websites for small businesses, eCommerce stores, agencies, freelancers and students across the country. Whether you are launching your first blog or reselling hosting to your own clients, the same infrastructure and the same support team stand behind your account.</p>
        </div>

        <!-- Mission & Vision -->
        <div class="about-mv-grid">
            <div class="about-mv-card">
                <div class="about-mv-icon"><i class="fas fa-bullseye"></i></div>
                <h3 data-i18n="about.mission.title">Our Mission</h3>
                <p data-i18n="about.mission.body">To remove every barrier between a good idea and a live website. That means honest pricing with no hidden renewal traps, infrastructure fast enough to compete globally, and support that treats a ৳999 customer exactly like a ৳9,999 one.</p>
            </div>
            <div class="about-mv-card accent">
                <div class="about-mv-icon"><i class="fas fa-eye"></i></div>
                <h3 data-i18n="about.vision.title">Our Vision</h3>
                <p data-i18n="about.vision.body">To become the hosting provider Bangladeshi businesses recommend by default — known for speed, uptime and a support team that actually solves problems instead of closing tickets.</p>
            </div>
        </div>

    </div>
</section>

<!-- ===========================
     WHAT WE OFFER
     =========================== -->
<section class="about-services-section">
    <div class="about-content-inner">

        <div class="about-section-heading">
            <h2 data-i18n="about.services.title">What We Offer</h2>
            <p data-i18n="about.services.subtitle">A complete hosting stack — from your first personal blog to a full reseller business.</p>
        </div>

        <div class="about-services-grid">

            <a href="<?php echo esc_url( hostorio_page_url( 'web-hosting' ) ); ?>" class="about-service-card">
                <span class="about-service-emoji">⚡</span>
                <h3 data-i18n="about.services.webHosting.title">Web Hosting</h3>
                <p data-i18n="about.services.webHosting.desc">NVMe shared hosting on LiteSpeed servers with free SSL, cPanel and unlimited bandwidth. Ideal for business sites, blogs and portfolios.</p>
                <span class="about-service-link" data-i18n="about.services.learnMore">View Plans <i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="<?php echo esc_url( hostorio_page_url( 'turbo-hosting' ) ); ?>" class="about-service-card">
                <span class="about-service-emoji">🚀</span>
                <h3 data-i18n="about.services.turbo.title">Turbo Hosting</h3>
                <p data-i18n="about.services.turbo.desc">Higher CPU and RAM allocation on our fastest nodes for traffic-heavy WordPress sites and growing eCommerce stores.</p>
                <span class="about-service-link" data-i18n="about.services.learnMore">View Plans <i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="<?php echo esc_url( hostorio_page_url( 'reseller-hosting' ) ); ?>" class="about-service-card">
                <span class="about-service-emoji">🔄</span>
                <h3 data-i18n="about.services.reseller.title">Reseller Hosting</h3>
                <p data-i18n="about.services.reseller.desc">Launch your own hosting brand with WHM, white-label cPanel accounts and full control over your client packages and pricing.</p>
                <span class="about-service-link" data-i18n="about.services.learnMore">View Plans <i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="<?php echo esc_url( hostorio_page_url( 'student-hosting' ) ); ?>" class="about-service-card">
                <span class="about-service-emoji">🎓</span>
                <h3 data-i18n="about.services.student.title">Student Hosting</h3>
                <p data-i18n="about.services.student.desc">Budget-friendly plans built for students, learners and first projects — the same servers and SSL, at a price that fits a student wallet.</p>
                <span class="about-service-link" data-i18n="about.services.learnMore">View Plans <i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="<?php echo esc_url( hostorio_page_url( 'vps' ) ); ?>" class="about-service-card">
                <span class="about-service-emoji">🖥️</span>
                <h3 data-i18n="about.services.vps.title">VPS Hosting</h3>
                <p data-i18n="about.services.vps.desc">Dedicated CPU, RAM and NVMe storage with full root access for developers and applications that have outgrown shared hosting.</p>
                <span class="about-service-link" data-i18n="about.services.learnMore">View Plans <i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="<?php echo esc_url( hostorio_page_url( 'business-email' ) ); ?>" class="about-service-card">
                <span class="about-service-emoji">✉️</span>
                <h3 data-i18n="about.services.email.title">Business Email</h3>
                <p data-i18n="about.services.email.desc">Professional email on your own domain with spam filtering, webmail, mobile sync and 2FA — no web hosting plan required.</p>
                <span class="about-service-link" data-i18n="about.services.learnMore">View Plans <i class="fas fa-arrow-right"></i></span>
            </a>

        </div>

    </div>
</section>

<!-- ===========================
     WHY CHOOSE US
     =========================== -->
<section class="about-content-section">
    <div class="about-content-inner">

        <div class="about-section-heading">
            <h2 data-i18n="about.why.title">Why Businesses Choose Hostorio</h2>
            <p data-i18n="about.why.subtitle">The things we refuse to compromise on, no matter which plan you are on.</p>
        </div>

        <div class="about-why-grid">

            <div class="about-why-card">
                <i class="fas fa-bolt"></i>
                <div>
                    <h3 data-i18n="about.why.speed.title">Genuinely Fast Infrastructure</h3>
                    <p data-i18n="about.why.speed.body">Every plan runs on LiteSpeed web servers with 100% pure NVMe storage and 1Gbps network connectivity — not spinning disks marketed as "SSD-class".</p>
                </div>
            </div>

            <div class="about-why-card">
                <i class="fas fa-headset"></i>
                <div>
                    <h3 data-i18n="about.why.support.title">Support in Bangla &amp; English</h3>
                    <p data-i18n="about.why.support.body">Our team answers tickets around the clock in the language you are comfortable with. No outsourced scripts, no timezone gap, no waiting days for a first reply.</p>
                </div>
            </div>

            <div class="about-why-card">
                <i class="fas fa-tags"></i>
                <div>
                    <h3 data-i18n="about.why.pricing.title">Transparent Taka Pricing</h3>
                    <p data-i18n="about.why.pricing.body">Prices are listed in BDT with a USD switcher, and we tell you the renewal price upfront. What you see at checkout is what you pay.</p>
                </div>
            </div>

            <div class="about-why-card">
                <i class="fas fa-shield-halved"></i>
                <div>
                    <h3 data-i18n="about.why.security.title">Security Included, Not Upsold</h3>
                    <p data-i18n="about.why.security.body">Free SSL certificates, proactive malware scanning and automated backups come standard — security should never be a paid add-on.</p>
                </div>
            </div>

            <div class="about-why-card">
                <i class="fas fa-arrows-rotate"></i>
                <div>
                    <h3 data-i18n="about.why.migration.title">Free, Hassle-Free Migration</h3>
                    <p data-i18n="about.why.migration.body">Moving from another host? Our team migrates your site, emails and databases for you, and we schedule the DNS switch so visitors never see downtime.</p>
                </div>
            </div>

            <div class="about-why-card">
                <i class="fas fa-chart-line"></i>
                <div>
                    <h3 data-i18n="about.why.scale.title">Room to Grow</h3>
                    <p data-i18n="about.why.scale.body">Start on shared hosting and upgrade to Turbo, VPS or Reseller whenever you outgrow it. Unused time on your old plan is credited toward the new one.</p>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ===========================
     TECHNOLOGY
     =========================== -->
<section class="about-tech-section">
    <div class="about-tech-inner">

        <div class="about-section-heading">
            <h2 data-i18n="about.tech.title">The Technology Behind Every Plan</h2>
            <p data-i18n="about.tech.subtitle">The same enterprise-grade stack powers our cheapest plan and our largest server.</p>
        </div>

        <div class="about-tech-grid">

            <div class="about-tech-item">
                <i class="fas fa-server"></i>
                <h3 data-i18n="about.tech.litespeed.title">LiteSpeed Servers</h3>
                <p data-i18n="about.tech.litespeed.desc">Up to 3× faster page delivery than standard Apache setups.</p>
            </div>

            <div class="about-tech-item">
                <i class="fas fa-hard-drive"></i>
                <h3 data-i18n="about.tech.nvme.title">100% NVMe Storage</h3>
                <p data-i18n="about.tech.nvme.desc">Pure NVMe drives for faster database queries and page loads.</p>
            </div>

            <div class="about-tech-item">
                <i class="fas fa-lock"></i>
                <h3 data-i18n="about.tech.ssl.title">Free SSL Certificates</h3>
                <p data-i18n="about.tech.ssl.desc">HTTPS on every domain and subdomain, auto-renewed for free.</p>
            </div>

            <div class="about-tech-item">
                <i class="fas fa-gauge-high"></i>
                <h3 data-i18n="about.tech.cpanel.title">cPanel Control Panel</h3>
                <p data-i18n="about.tech.cpanel.desc">The industry-standard panel with 1-click app installs.</p>
            </div>

        </div>

    </div>
</section>

<!-- ===========================
     CONTACT / CTA
     =========================== -->
<section class="about-contact-section">
    <div class="about-contact-box">

        <h2>
            <i class="fas fa-comments"></i>
            <span data-i18n="about.contact.title">Talk to a Real Person</span>
        </h2>
        <p data-i18n="about.contact.body">Not sure which plan fits your project? Tell us what you are building and we will recommend the right one — no upselling, no pressure. Our team is here whenever you need us.</p>

        <ul class="about-contact-list">
            <li>
                <i class="fas fa-life-ring"></i>
                <span>
                    <strong data-i18n="about.contact.supportLabel">Support Portal:</strong><br>
                    <a href="https://my.hostorio.com/submitticket.php">my.hostorio.com/submitticket.php</a>
                </span>
            </li>
            <li>
                <i class="fas fa-envelope"></i>
                <span>
                    <strong data-i18n="about.contact.emailLabel">Email:</strong><br>
                    <a href="mailto:<?php echo esc_attr( hostorio_get( 'business_email', 'mail@hostorio.com' ) ); ?>"><?php echo esc_html( hostorio_get( 'business_email', 'mail@hostorio.com' ) ); ?></a>
                </span>
            </li>
            <li>
                <i class="fas fa-phone-alt"></i>
                <span>
                    <strong data-i18n="about.contact.phoneLabel">Phone &amp; WhatsApp:</strong><br>
                    <a href="tel:<?php echo esc_attr( hostorio_get( 'business_phone', '+8801779440297' ) ); ?>"><?php echo esc_html( hostorio_get( 'business_phone', '+8801779440297' ) ); ?></a>
                </span>
            </li>
            <li>
                <i class="fas fa-location-dot"></i>
                <span>
                    <strong data-i18n="about.contact.addressLabel">Office:</strong><br>
                    <?php echo esc_html( hostorio_get( 'business_address', 'H-161, OABDA SOROK MATHBARIA' ) ); ?>,
                    <?php echo esc_html( hostorio_get( 'business_city', 'Kanchan, Rupganj, Narayanganj' ) ); ?>
                </span>
            </li>
        </ul>

        <div class="about-contact-actions">
            <a href="<?php echo esc_url( hostorio_page_url( 'web-hosting' ) ); ?>" class="about-btn about-btn-primary" data-i18n="about.contact.ctaPlans">
                <i class="fas fa-rocket"></i> Explore Hosting Plans
            </a>
            <a href="https://my.hostorio.com/submitticket.php" class="about-btn about-btn-outline" data-i18n="about.contact.ctaSupport">
                <i class="fas fa-headset"></i> Contact Support
            </a>
        </div>

        <p class="about-contact-note" data-i18n="about.contact.note">Tickets are answered 24/7. Phone support is available Saturday through Thursday, 9:00 AM – 9:00 PM (BST).</p>

    </div>
</section>

<?php get_footer(); ?>
