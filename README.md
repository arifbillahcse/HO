# Hostorio Laravel Web Hosting Platform

A modern Laravel 13 application converted from the WordPress Hostorio theme. Fast, SEO-optimized marketing website for web hosting services with a built-in admin panel.

## Features

✨ **Modern Architecture**
- Laravel 13.25.0 with Blade templating
- SQLite database with migrations
- Client-side i18n system (English & Bengali)
- Responsive design with Font Awesome 6.5.2

🎨 **12 Full-Featured Pages**
- Homepage with hero section
- Web Hosting, Turbo Hosting, Reseller Hosting, Student Hosting
- VPS Hosting with pricing tiers
- Business Email Solutions
- Terms & Conditions, Privacy Policy, About Us
- Special Offers page
- Dynamic 404 error page

🔧 **Admin Panel**
- Filament admin dashboard at `/admin`
- Settings management interface
- Default admin: `admin@hostorio.com` / `admin123`

⚡ **Performance**
- Settings cached with 1-hour TTL
- Optimized database queries
- SEO-friendly URL structure

📱 **SEO & Crawlability**
- Dynamic sitemap.xml at `/sitemap.xml`
- robots.txt for search engines
- Meta tags and OpenGraph support
- Canonical URLs and JSON-LD data

🌍 **Multilingual Support**
- English & Bengali translations
- Currency switching (BDT/USD)
- Dynamic exchange rate configuration

## Quick Start

### Installation

```bash
# Clone repository
git clone https://github.com/arifbillahcse/laravel-ho.git
cd laravel-ho

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Run dev server
php artisan serve
```

**Access:**
- Website: http://localhost:8000
- Admin Panel: http://localhost:8000/admin
- Admin Credentials: `admin@hostorio.com` / `admin123`

## Deployment

### Laravel Forge (Recommended)

1. Connect GitHub repo to Forge
2. Create server (Ubuntu 24.04)
3. Deploy via Forge UI
4. Set environment variables in Forge dashboard
5. Run migration: `php artisan migrate --force`

### Manual VPS Deployment

```bash
# SSH to VPS
ssh deploy@your-vps-ip

# Install PHP, MySQL, Nginx
sudo apt install php8.2-fpm mysql-server nginx composer

# Clone and setup
cd /var/www
git clone https://github.com/arifbillahcse/laravel-ho.git hostorio
cd hostorio
composer install --no-dev
cp .env.example .env
php artisan key:generate
php artisan config:cache

# Database setup
php artisan migrate --force
php artisan db:seed

# Permissions
sudo chown -R www-data:www-data /var/www/hostorio
sudo chmod -R 755 /var/www/hostorio
sudo chmod -R 775 storage
```

Configure Nginx and enable HTTPS with Let's Encrypt (see README for full config).

## Settings Management

Access via admin panel at `/admin`:

**Available Settings:**
- Business info (name, phone, email, address)
- Social media links (Facebook, Twitter, YouTube, LinkedIn)
- Logo URL and footer description
- Client area URL
- Exchange rate (BDT to USD)
- Default currency and language

## Pages

- `/` - Homepage
- `/web-hosting` - Web Hosting
- `/turbo-hosting` - Turbo Hosting
- `/reseller-hosting` - Reseller Hosting
- `/student-hosting` - Student Hosting
- `/vps` - VPS Hosting
- `/business-email` - Business Email
- `/about-us` - About Us
- `/offer` - Special Offers
- `/terms-and-conditions` - Terms & Conditions
- `/privacy-policy` - Privacy Policy
- `/package` - Hosting Packages

## SEO Features

- **Sitemap**: `/sitemap.xml` (auto-generated, 1h cache)
- **Robots.txt**: `/robots.txt` (search engine crawl rules)
- **Meta tags**: Description, OpenGraph, Canonical URLs
- **JSON-LD**: Structured data for rich snippets
- **Mobile responsive**: Works perfectly on all devices

## Admin Panel

**Access**: `https://yoursite.com/admin`

**Features:**
- List all settings with search
- Edit individual settings
- Changes reflected immediately (cache: 1 hour)
- User account management

**Default Login:**
- Email: `admin@hostorio.com`
- Password: `admin123`

⚠️ Change password immediately in production!

## Project Structure

```
laravel-ho/
├── app/
│   ├── Filament/              # Admin panel (Filament)
│   ├── Http/Controllers/      # Request handlers
│   ├── Models/Setting.php     # Settings model
│   └── Services/              # Business logic
├── resources/views/
│   ├── pages/                 # 12 page templates
│   ├── partials/              # Header, footer
│   ├── errors/404.blade.php   # Error page
│   └── layouts/app.blade.php  # Master layout
├── public/
│   ├── assets/                # CSS, JS, images
│   ├── robots.txt             # SEO crawler rules
│   └── sitemap.xml            # Dynamic sitemap
├── database/
│   ├── migrations/            # Database schema
│   └── seeders/               # Initial data
└── routes/web.php             # Application routes
```

## Helpers

### Get Settings in Blade
```blade
{{ hostorio('business_phone', '+8801779440297') }}
{{ hostorio('logo_url') }}
```

### Generate URLs
```blade
{{ route('home') }}
{{ route('page', 'web-hosting') }}
```

## Cache Management

Settings are cached with 1-hour TTL:

```php
// In code
use App\Services\SettingsService;
SettingsService::flush(); // Clear cache
```

Or via Artisan:
```bash
php artisan cache:clear
```

## Performance

- Settings cached for 1 hour
- Asset caching headers (images: 1 year)
- Optimized database queries
- Gzip compression (Nginx)
- CDN-ready for static assets

## Security

✅ Admin panel requires authentication
✅ CSRF protection
✅ XSS protection (Blade auto-escaping)
✅ robots.txt blocks /admin/
✅ Environment variables in .env

📋 Checklist:
- [ ] Change admin password (production)
- [ ] Enable HTTPS (Let's Encrypt)
- [ ] Setup firewall rules
- [ ] Configure backups
- [ ] Monitor error logs

## Troubleshooting

**404 pages not showing:**
- Verify view files in `resources/views/pages/`
- Clear view cache: `php artisan view:clear`

**Admin panel inaccessible:**
- Check database connection
- Create admin: `php artisan db:seed --class=AdminUserSeeder`

**Settings not updating:**
- Clear cache: `php artisan cache:clear`
- Check database permissions

**Sitemap not generating:**
- Clear routes: `php artisan route:clear`
- Verify `/sitemap.xml` route exists

## Support

- GitHub: https://github.com/arifbillahcse/laravel-ho
- Issues: GitHub Issues
- Documentation: README.md

## License

MIT License - See LICENSE file

## Credits

- **Conversion**: Claude Haiku 4.5 (Anthropic)
- **Original Theme**: WordPress Hostorio Theme
- **Framework**: Laravel 13 by Taylor Otwell
- **Admin**: Filament by Dan Harrin

---

**Version**: 1.0.0 | **Status**: Production Ready | **Last Updated**: September 2026
