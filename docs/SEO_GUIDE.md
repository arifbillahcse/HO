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

## Redirects

**Admin → SEO → Redirects** sends visitors and search engines from an old address to a new one.

- **Old address**: a path (`/old-page/`) or a full URL; case and the trailing `/` don't matter. End it with `*` to
  match everything that starts with it: `/blog/*`.
- **New address**: a page on this site (`/web-hosting/`) or a full URL. With a `*` old address, a `*` in the new
  address is replaced by the rest of the address (`/blog/*` → `/news/*` sends `/blog/my-post` to `/news/my-post`).
- **Type**: **301** for pages that moved for good (search engines pass the ranking on), **302/307** for temporary
  moves, **410** for pages deleted for good.
- Each redirect counts its **hits**, so you can see which ones are still used. Switch **Active** off to pause one.
- The homepage, the whole site, `/admin`, the sitemap and robots.txt can't be redirected.

## 404 Monitor

**Admin → SEO → 404 Monitor** lists addresses people or search engines asked for that don't exist, with how often
and where they came from. Click **Redirect** on a row (or tick several rows → **Redirect selected**) to create a
redirect; the entry then leaves the log. Bots probing for `/wp-login.php` and similar can simply be deleted.
The log keeps the latest 2,000 addresses.

## Sitemap and robots.txt

- `/sitemap.xml` lists every page that is set to index, with the date it last changed (its SEO settings, plans,
  comparison table or template). The old Rank Math addresses (`/sitemap_index.xml`, `/page-sitemap.xml`, …)
  redirect to it.
- `/robots.txt` can be edited in **SEO Settings → robots.txt**. Leave it empty for the default, which keeps search
  engines out of `/admin/` and points them to the sitemap. "Disallow: /" is refused: use
  **Hide this site from search engines** for the demo instead.

## Launch checklist

**Admin → SEO → Launch checklist** checks this installation automatically (search engine visibility, debug mode,
`APP_URL`, storage link, trailing-slash rules, robots.txt, sitemap, descriptions, images, analytics, verification)
and lists the steps for launch day. Open it on hostorio.com right after the switch: everything should be green
apart from the blue notes.
