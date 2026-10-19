# Domains: the Domain Registration page

`https://hostorio.com/domain-registration/` shows a domain search box and a price table for every extension your
WHMCS sells, with live register, renew and transfer prices. It is linked from the **Domain** menu (Domain
Registration, Domain Pricing), the footer and the homepage.

## Where the prices come from

- WHMCS, through the connection under **Admin → WHMCS Connection** (the same one the homepage hero uses).
  Change a price in WHMCS and the page follows; nothing to edit twice.
- Prices refresh on the schedule set there (6 hours by default). **Domains → Domain prices → Refresh from WHMCS**
  fetches them straight away. If WHMCS is unreachable, the last prices stay on the page.
- The table shows BDT. If WHMCS gives USD, it is converted with the site's exchange rate. The BDT/USD switch at
  the top of the site works on these prices too.
- Without a working WHMCS connection the page still loads, with a "View domain prices" button to your client
  area instead of the table.

## Admin → Domains → Domain prices

Every extension WHMCS sells, with its prices. New extensions added in WHMCS appear here by themselves.

- **Order**: click the ↕ button, drag rows, click it again. The website uses the same order.
- **Badge**: *As in WHMCS* uses the group you set in WHMCS (HOT, NEW or SALE); or choose a badge, or *No badge*.
- **Show on website**: switch off to hide an extension from the page.

## Admin → Domains → Domain page

The questions and answers (FAQ) on the page, in English. Put a live price in an answer with
`{register:.com}`, `{renew:.com}` or `{transfer:.com}` for any extension, e.g. "A .com domain costs
{register:.com}". Google gets the same questions as FAQ data.

The page's title, description and focus keyword for Google are under **SEO → Page SEO → Domain Registration**.
The other texts (headings, "Why register with Hostorio", the 3 steps) are in English and Bangla in
`public/assets/js/translations/domain/registration.js`.

## How ordering works for visitors

The search box sends the name to the WHMCS cart. In the price table, **Register** puts that extension into the
search box (e.g. "mybrand" becomes "mybrand.com.bd") and starts the search; with an empty box it fills in the
extension and places the cursor so the name can be typed.
