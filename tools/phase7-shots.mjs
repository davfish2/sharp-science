// Usage: node tools/phase7-shots.mjs <before|after>. Full-page shots of the six key templates at 375 and 1440.
import { chromium } from 'playwright';
import fs from 'node:fs';
const tag = process.argv[2] || 'after', base = 'http://localhost:8888', OUT = 'review/phase7';
fs.mkdirSync(OUT, { recursive: true });
const b = await chromium.launch();
const settle = async (p) => {
  await p.evaluate(async () => {
    document.querySelectorAll('img[loading=lazy]').forEach((i) => { i.loading = 'eager'; });
    await Promise.all([...document.images].map((i) => i.decode().catch(() => {})));
    await document.fonts.ready;
  });
  await p.waitForTimeout(300);
};
for (const w of [375, 1440]) {
  const ctx = await b.newContext({ viewport: { width: w, height: w < 768 ? 812 : 900 }, deviceScaleFactor: w < 768 ? 2 : 1 });
  await ctx.addCookies([{ name: 'ss_entry', value: '1', url: base }]);
  const p = await ctx.newPage();
  const shot = async (n, u) => {
    await p.goto(base + u, { waitUntil: 'networkidle' });
    if (n === 'checkout') { await p.waitForSelector('.wc-block-checkout'); await p.waitForTimeout(1200); }
    await settle(p);
    await p.screenshot({ path: `${OUT}/${tag}-${n}-${w}.png`, fullPage: true });
  };
  await shot('home', '/');
  await shot('category', '/shop/vials-storage/');
  await shot('product', '/product/borosilicate-serum-vial-10ml/');
  await shot('certificates', '/certificates/');
  await p.goto(base + '/product/griffin-beaker-250ml/', { waitUntil: 'networkidle' });
  await p.click('.buy button'); await p.waitForSelector('#cart-drawer:not([hidden]) .mini');
  await shot('cart', '/cart/');
  await shot('checkout', '/checkout/');
  await ctx.close();
}
await b.close();
console.log('shots done', tag);
