# Progress

## Setup and Phase 0: reference measurement
- Built: repo setup, tooling, `tools/capture-site.mjs`, `/research`.
- Result: blocked on all four sites. Only type measurements from primepeptides.co are usable. See `/research/SUMMARY.md`.

## Phase 1: design system
- Built: `design/tokens.css`, `design/tokens.json`, `design/contrast.json`, self-hosted fonts, `theme.json`, `design/brand-sheet.html`.
- View: open `design/brand-sheet.html`. Screenshots: `review/brand-sheet-390.png`, `review/brand-sheet-1440.png`.
- Contrast: every permitted pair passes AA after decisions 5 and 6.

## Phase 2: logo and label (awaiting review)
- Built: six logo SVGs, favicon set, social image, 16 label SVGs, print PDF, 16 renders.
- Files: `brand/logo`, `brand/favicon`, `brand/social`, `brand/labels`, `brand/renders`.
- Checked: 16 of 16 QR codes decode to the right batch URL. The PDF has 16 pages with a 50 x 22 mm TrimBox and a 56 x 28 mm BleedBox.

## Phase 3: WordPress and WooCommerce
- Built: `sharp-science` theme and `sharp-science-core` plugin, `.wp-env.json`, `tools/seed.sh`.
- View: `npx wp-env start && tools/seed.sh`, then http://localhost:8888. Admin at /wp-admin, `admin` / `password`.
- Generation: label SVG, label PDF and render SVG are written in pure PHP on save. The admin browser converts the render to WebP.
- Checked: a guest order placed by Playwright, blocked until the confirmation was ticked. Total $24.45, confirmation text and timestamp stored. 16 of 16 PHP QR codes decode.

## Phase 4: certificates and settings
- Built: `certificate` post type, /certificates/ search and filter, product page rows, one settings page, eight draft pages marked [CLIENT TO REVIEW].

## Phase 5: demo products
- Built: 16 products, 6 sample certificates, re-runnable import (`wp sharp import`, `tools/seed.sh`). Certificates and their PDFs state they are sample data.
