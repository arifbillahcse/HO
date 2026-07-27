# Hostorio WordPress Theme

Custom WordPress theme for [Hostorio](https://hostorio.com) — a web hosting services provider based in Bangladesh. Converted from a static HTML/CSS/JS site into a fully functional WordPress theme.

## Features

- **Multi-language support** — English and Bangla (BN) via client-side i18n
- **Currency switcher** — BDT / USD with configurable exchange rate
- **SEO optimized** — Schema.org JSON-LD markup, Open Graph, Twitter Cards (defers to Yoast/RankMath when installed)
- **Responsive design** — Mobile navigation drawer, adaptive layouts
- **LiteSpeed compatible** — Designed for LiteSpeed + NVMe hosting environments
- **Admin settings panel** — Manage business info, social links, exchange rate, and client area URL from the dashboard

## Requirements

- WordPress 5.9+
- PHP 7.4+

## Installation

1. Copy the `hostorio` folder into `wp-content/themes/`
2. In the WordPress admin, go to **Appearance > Themes** and activate **Hostorio**
3. On activation, the theme automatically creates pages for each template (Home, Web Hosting, Turbo Hosting, etc.) and sets the Home page as the static front page

## Theme Structure

```
hostorio/
├── assets/
│   ├── css/styles.css          # All styles (preserved from original)
│   ├── images/                 # Logos, hero, pricing, support, testimonials
│   └── js/
│       ├── animations.js       # Scroll animations
│       ├── billing.js          # Billing toggle (monthly/annual)
│       ├── countdown.js        # Offer page countdown timer
│       ├── header.js           # Header scroll & mobile menu
│       ├── i18n.js             # Internationalization engine
│       ├── main.js             # App entry point
│       ├── pricing.js          # Pricing card logic
│       ├── translations.js     # Translation registry
│       ├── translations/       # Per-page translation files (EN/BN)
│       └── utils.js            # Utility helpers
├── inc/
│   ├── admin-settings.php      # Settings page under Appearance > Hostorio Settings
│   └── theme-activation.php    # Auto-creates pages & sets static front page
├── page-templates/
│   ├── page-home.php           # Home (index.html)
│   ├── page-web-hosting.php    # Web Hosting
│   ├── page-turbo-hosting.php  # Turbo Hosting
│   ├── page-reseller-hosting.php
│   ├── page-student-hosting.php
│   ├── page-business-email.php
│   ├── page-vps.php
│   ├── page-offer.php          # Offer / Deals (with countdown)
│   ├── page-package.php        # Package comparison
│   ├── page-privacy.php        # Privacy Policy
│   └── page-terms.php          # Terms & Conditions
├── 404.php
├── footer.php
├── front-page.php              # Routes to page-home.php
├── functions.php               # Theme setup, asset enqueuing, helpers
├── header.php                  # Top bar, navigation, mobile nav
├── index.php                   # Fallback template
├── page.php                    # Generic page template
└── style.css                   # Theme metadata
```

## Page Templates

Each HTML page maps to a WordPress page template:

| HTML Source               | Page Template              | WP Page Title       |
|---------------------------|----------------------------|---------------------|
| `index.html`              | `page-home.php`            | Home                |
| `web-hosting.html`        | `page-web-hosting.php`     | Web Hosting         |
| `turbo-hosting.html`      | `page-turbo-hosting.php`   | Turbo Hosting       |
| `reseller-hosting.html`   | `page-reseller-hosting.php`| Reseller Hosting    |
| `student-hosting.html`    | `page-student-hosting.php` | Student Hosting     |
| `business-email.html`     | `page-business-email.php`  | Business Email      |
| `vps.html`                | `page-vps.php`             | VPS                 |
| `offer.html`              | `page-offer.php`           | Offer               |
| `package.html`            | `page-package.php`         | Package             |
| `privacy-policy.html`     | `page-privacy.php`         | Privacy Policy      |
| `terms-and-conditions.html`| `page-terms.php`          | Terms & Conditions  |

## Admin Settings

Navigate to **Appearance > Hostorio Settings** to configure:

- **Client Area URL** — Base URL for WHMCS/client portal (default: `https://my.hostorio.com/`)
- **Business Name, Phone, Email, Address**
- **Logo URL**
- **Social Media Links** — Facebook, Twitter, YouTube, LinkedIn
- **USD Exchange Rate** — Used by the currency switcher

## Customizer Options

Under **Appearance > Customize**:

- **Footer Description** text
- **Copyright** text
- **Social media** URLs (Facebook, Twitter, YouTube, LinkedIn)

## How It Works

- `header.php` and `footer.php` provide the shared layout (top info bar, navigation, footer columns)
- Each page template contains the full body content from its corresponding HTML file
- Static asset paths (`./assets/...`) are replaced with `<?php echo esc_url( HOSTORIO_URI ); ?>/assets/...`
- Internal links between pages use the `hostorio_page_url()` helper, which resolves page slugs to WordPress permalinks
- External links to the client area use `hostorio_get( 'client_area_url' )` so the base URL is configurable
- All output is escaped with `esc_url()`, `esc_html()`, or `esc_attr()` per WordPress coding standards

## License

GPL-2.0-or-later
