# Sharp Science

Pitch-demo WooCommerce store for an Australian laboratory supplier.

## Requirements
Node 22, Docker (OrbStack works), uv.

## Set up
```sh
npm install
npx playwright install chromium
uv venv --python 3.12 .venv && uv pip install --python .venv/bin/python fonttools brotli "scrapling[fetchers]"
```

## Rebuild the brand assets
```sh
.venv/bin/python tools/make-fonts.py   # static fonts for outlining
node tools/contrast.mjs                # WCAG ratios
node tools/build-logo.mjs              # lockups, favicons, social image
node tools/build-labels.mjs            # label SVGs and print PDF (LABEL_W, LABEL_H, QR_BASE env vars)
node tools/build-renders.mjs           # 1600 x 1600 WebP renders
node tools/check-labels.mjs            # confirms each QR decodes
node tools/build-brand-sheet.mjs       # design/brand-sheet.html
```
Open `design/brand-sheet.html` in a browser to review.

## Run WordPress locally
The `wp-env` setup arrives in Phase 3.
