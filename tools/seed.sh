#!/usr/bin/env bash
# Re-runnable demo seed: store setup, products, labels. Run with wp-env started.
set -euo pipefail
cd "$(dirname "$0")/.."
wp() { npx wp-env run cli wp "$@" 2>&1 | grep -vE "Starting 'wp|Ran \`wp" ; }
wp theme activate sharp-science
wp sharp setup --pages=/var/www/html/sharp-data/pages
wp sharp import /var/www/html/sharp-data/products.csv
wp sharp certificates
wp rewrite flush
node tools/rasterise.mjs
