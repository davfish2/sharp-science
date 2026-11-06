#!/usr/bin/env bash
# Usage: tools/audit.sh <lighthouse-dir> <out-dir>. Lighthouse mobile on the four key templates.
LH="$1/node_modules/.bin/lighthouse"; OUT="$2"; mkdir -p "$OUT"
export CHROME_PATH="$(ls -d ~/Library/Caches/ms-playwright/chromium_headless_shell-1254/*/chrome-headless-shell)"
for p in "home:/" "category:/shop/vials-storage/" "product:/product/borosilicate-serum-vial-10ml/" "certificates:/certificates/"; do
  n=${p%%:*}; u=${p#*:}
  "$LH" "http://localhost:8888$u" --quiet --output=json --output-path="$OUT/$n.json" --only-categories=performance,accessibility,best-practices,seo --chrome-flags="--headless --no-sandbox" --extra-headers='{"Cookie":"ss_entry=1"}' >/dev/null 2>&1
  node -e "const r=JSON.parse(require('fs').readFileSync('$OUT/$n.json'));const c=r.categories;console.log('$n',['performance','accessibility','best-practices','seo'].map(k=>Math.round(c[k].score*100)).join(' / '),'LCP',r.audits['largest-contentful-paint'].displayValue,'TBT',r.audits['total-blocking-time'].displayValue,'CLS',r.audits['cumulative-layout-shift'].displayValue);for(const k of ['accessibility','best-practices','seo','performance'])for(const a of c[k].auditRefs){const x=r.audits[a.id];if(a.weight>0&&x.score!==null&&x.score<0.9)console.log('   ',k,a.id,x.displayValue||'')}"
done
