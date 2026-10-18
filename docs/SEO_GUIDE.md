# SEO: how to use it

Everything Rank Math did on the WordPress site is under **`/admin` → SEO**. Changes show on the website as soon
as you save.

## SEO Settings (whole site)

| Section | What it does |
|---|---|
| **Titles** | Site name, separator and title pattern. With the defaults every title looks like the Rank Math ones: `Web Hosting - Fast Web Hosting ৳999/yr \| Free SSL & Domain \| Hostorio`. Variables: `%title%` (page name), `%sep%` (separator), `%sitename%` (site name). |
| **Search engine visibility** | **Hide this site from search engines**: for the demo site only. Adds "noindex" to every page and turns off Google Analytics and Tag Manager. Must be **off** on hostorio.com. |
| **Images** | **Default share image** (1200 × 630) for Facebook/X/LinkedIn/WhatsApp previews, and the **site icon** (favicon), a square PNG of at least 512 × 512. |
| **Webmaster tools verification** | Google, Bing, Yandex, Baidu, Pinterest and Facebook codes. Paste the code or the whole `<meta>` tag; both work. Not needed if you verified with a DNS record. |
| **Analytics** | Google Analytics 4 ID (already set to `G-PJSW407GJS`) and an optional Tag Manager ID. Use one or the other, not both, or visits are counted twice. |
| **Custom code** | Code added to every page, in `<head>` or before `</body>`. The chat widget is already in "Code before `</body>`". |

## Page SEO (each page)

**Admin → SEO → Page SEO** lists the 11 pages with their title in Google, whether they have a description and
whether they are indexed. Click **Edit**:

- **Google** tab: a live Google preview, then:
  - **SEO title**: leave empty to use the pattern. You can use the same variables, e.g. `Best %title% in Bangladesh %sep% Hostorio`. Keep it under about 60 characters.
  - **Meta description**: 120–160 characters. Empty means Google writes its own snippet.
  - **Focus keyword**: the main phrase you want the page to rank for.
- **Social** tab: a separate title, description and image for shares. Empty fields use the SEO title, the
  description and the default share image.
- **Advanced** tab:
  - **Page name**: what `%title%` becomes.
  - **index / follow** switches. Switching off "index" adds noindex and removes the page from the sitemap.
  - **Canonical URL**: leave empty unless another address should rank instead.

Use **View page** to open the page. To check the result, right-click → View page source and look at the
`<title>` and `<meta>` lines at the top.

## Page addresses

Addresses end in `/`, exactly as on WordPress (`https://hostorio.com/web-hosting/`). Opening an address without the
slash redirects (301) to the one with it, so Google sees no change of URL when the site moves from WordPress.
The sitemap is at `/sitemap.xml`.
