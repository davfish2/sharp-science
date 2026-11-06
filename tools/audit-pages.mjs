// axe on every template at both widths, plus JS weight, source checks and review screenshots.
// Usage: node tools/audit-pages.mjs <path-to-axe.min.js>
import { chromium } from 'playwright';
import fs from 'node:fs';
const axe = fs.readFileSync(process.argv[2], 'utf8'), base = 'http://localhost:8888', OUT = 'review/screens';
fs.mkdirSync(OUT, { recursive: true });
const pages = { home: '/', shop: '/shop/', category: '/shop/vials-storage/', product: '/product/borosilicate-serum-vial-10ml/', certificates: '/certificates/', 'certificates-batch': '/certificates/?batch=B26-1015', search: '/?s=flask&post_type=product', '404': '/nope/', 'cart-empty': '/cart/', terms: '/terms-of-sale/', faq: '/faq/', about: '/about/', contact: '/contact/', account: '/my-account/' };
const b = await chromium.launch(); const issues = [];
const scan = async (p, name) => {
  await p.addScriptTag({ content: axe });
  const r = await p.evaluate(() => window.axe.run(document, { runOnly: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'best-practice'] }));
  for (const v of r.violations) issues.push({ page: name, id: v.id, impact: v.impact, nodes: v.nodes.length, sample: v.nodes[0].html.slice(0, 110) });
};
const settle = async (p) => { await p.evaluate(async () => { document.querySelectorAll('img[loading=lazy]').forEach(i => { i.loading = 'eager'; }); await new Promise(r => setTimeout(r, 300)); }); };
for (const w of [390, 1440]) {
  const ctx = await b.newContext({ viewport: { width: w, height: 900 } });
  await ctx.addCookies([{ name: 'ss_entry', value: '1', url: base }]);
  const p = await ctx.newPage();
  for (const [n, u] of Object.entries(pages)) {
    await p.goto(base + u, { waitUntil: 'networkidle' }); await settle(p);
    const over = await p.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (over > 0) issues.push({ page: `${n}@${w}`, id: 'horizontal-overflow', impact: 'layout', nodes: over });
    await p.screenshot({ path: `${OUT}/${n}-${w}.png`, fullPage: true }); await scan(p, `${n}@${w}`);
  }
  await p.goto(base + '/product/griffin-beaker-250ml/', { waitUntil: 'networkidle' });
  await p.click('.buy button'); await p.waitForSelector('#cart-drawer:not([hidden]) .mini');
  await p.screenshot({ path: `${OUT}/cart-drawer-${w}.png` }); await scan(p, `drawer@${w}`);
  await p.goto(base + '/cart/', { waitUntil: 'networkidle' }); await p.screenshot({ path: `${OUT}/cart-${w}.png`, fullPage: true }); await scan(p, `cart@${w}`);
  await p.goto(base + '/checkout/', { waitUntil: 'networkidle' }); await p.waitForSelector('.wc-block-checkout'); await p.waitForTimeout(1200);
  await p.screenshot({ path: `${OUT}/checkout-${w}.png`, fullPage: true }); await scan(p, `checkout@${w}`);
  await ctx.close();
  const g = await (await b.newContext({ viewport: { width: w, height: 900 } })).newPage();
  await g.goto(base + '/', { waitUntil: 'networkidle' }); await g.screenshot({ path: `${OUT}/entry-notice-${w}.png` }); await scan(g, `notice@${w}`);
  await g.context().close();
}
const summary = {}; for (const i of issues) { const k = `${i.impact}: ${i.id}`; (summary[k] ??= { pages: [], sample: i.sample }).pages.push(i.page); }
console.log(JSON.stringify(summary, null, 1));
fs.writeFileSync('review/axe-results.json', JSON.stringify(issues, null, 1));

const p = await (await b.newContext({ viewport: { width: 390, height: 844 } })).newPage(); let js = 0, jsRaw = []; 
p.on('response', async r => { if (r.url().endsWith('.js') || r.url().includes('.js?')) { const l = (await r.body().catch(() => Buffer.alloc(0))).length; js += l; jsRaw.push(r.url().split('/').pop() + ':' + l); } });
await p.goto(base + '/', { waitUntil: 'networkidle' });
console.log('home JS bytes', js, jsRaw);
await b.close();
