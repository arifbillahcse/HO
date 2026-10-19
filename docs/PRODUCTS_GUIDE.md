# Products & Services: how to edit plans

Every plan card and comparison table on the website is managed from the admin panel at
**`/admin` → Products & Services**. Changes show on the website as soon as you save.
There is nothing to deploy.

Plan text is **English only**. When a visitor switches the site to বাংলা, the page around the plans is
translated, but plan names, descriptions, specifications and table values stay exactly as you typed them.

## Which page shows what

| Page | Plan cards | Comparison table |
|---|---|---|
| Home | yes | — |
| Web Hosting | yes | yes |
| Turbo Hosting | yes | yes |
| Reseller Hosting | yes | yes |
| Student Hosting | yes | yes |
| VPS | yes | — |
| Business Email | yes | — |
| Offer | yes | yes |

The Home and Web Hosting pages have **separate** product lists. If you change the Web Hosting "Starter"
plan, change it on Home too.

## Products

**Products & Services → Products** lists every plan, grouped by page.

- **Change the order:** click the ↕ reorder button at the top of the table, drag the rows, then click it again to finish.
  The website uses the same order, left to right.
- **Hide a plan:** switch off **Visible**. It disappears from the cards and the comparison table, but nothing is deleted.
- **Copy a plan:** **⋮ → Duplicate**. The copy is named "… (copy)", is hidden, and opens straight away for editing.
  Switch **Show on website** on when it is ready.
- **Delete a plan:** **⋮ → Delete**. This cannot be undone. Hiding is usually the better choice.

### Editing a plan

Click a plan's name. The form has four tabs.

**Card**
- **Page**: which page the plan belongs to.
- **Plan name**, **Short description**, **Button text**: shown on the card.
- **Highlight this plan**: orange border plus a badge. **Badge text** is what the badge says (for example "Recommended").
  Only highlight one plan per page.
- **Show crossed-out price and "Save" badge**: the old price with a line through it, plus "Save 30%".
- **Icon**: optional image (PNG, JPG or WebP, up to 1 MB). Leave empty for the standard server icon.

**Specifications**: the ticked list on the card.
- **Bold value** is the bold part (`10GB`), **Text** is the rest (`NVMe Storage`). Leave the value empty for plain text.
- Switch on **NEW tag** to add a small "NEW" label.
- Drag rows to reorder them; use **Add specification** for a new line.

**Comparison** (only on pages that have a comparison table): one line per table row.
- **Yes** shows a green tick, **No** a red cross, **Text** shows the words you type (for example `75GB NVMe`).

**Price & WHMCS**
- **WHMCS product ID**: the product's ID in WHMCS (WHMCS admin → System Settings → Products/Services,
  the number in the product's edit link, `id=55`). When it is set:
  - the price comes live from WHMCS (the yearly BDT price; if WHMCS only has USD, USD × the site's exchange rate), and
  - the order button opens the WHMCS cart for that product.

  Without an ID (and without an order link) the button opens the WHMCS store and the fallback price is shown.
  **Admin → SEO → Launch checklist** lists the plans that still have no ID.
- **Fallback yearly price** (BDT): used when the product has no WHMCS ID, or when WHMCS is unreachable.
  Always fill this in. Monthly prices on the site are worked out from the yearly price.
- **Order link (optional)**: a custom order link. If the link contains `pid=…`, that number is replaced with
  the WHMCS product ID above, so the link stays correct if you change the ID.

Click **Save changes**. Open the page on the website to check it (press Ctrl + F5 if the old version still shows).

## Comparison rows

**Products & Services → Comparison rows** holds the row names of each comparison table (the left-hand column),
for example "Storage Space".

- **Add a row**: **New comparison row**, pick the page, type the label. Then open each plan on that page,
  go to its **Comparison** tab and fill in the new line. Until you do, the cell is empty.
- **Rename a row**: click it and edit the label. Press Enter inside the label for a line break.
- **Reorder rows**: use the reorder button, as with products.
- **Delete a row**: removes the line from the table for every plan on that page.

## Prices from WHMCS

Live prices need the connection set up once under **WHMCS Connection** (API URL, identifier and secret).
Prices are cached (6 hours by default, set on that page). To fetch new prices immediately after changing them
in WHMCS, open **WHMCS Connection** and click **Save and sync**.

If WHMCS is down, the site keeps showing the last prices it received, or the fallback prices if it never
received any. Pages never break because of WHMCS.

## Quick checklist for a new plan

1. **Products → New product**, choose the page.
2. Fill in name, description, specifications.
3. Fill in the **Comparison** tab (if the page has a table).
4. Enter the **WHMCS product ID** and a **Fallback yearly price**.
5. Save, then drag it into place in the list.
