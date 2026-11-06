// Usage: node tools/view.mjs <path> <out-prefix> [widths]. Screenshots a live page past the entry notice.
import { chromium } from 'playwright';
const [path, out, ws = '1440,390'] = process.argv.slice(2);
const b = await chromium.launch();
for (const w of ws.split(',').map(Number)) {
  const ctx = await b.newContext({ viewport: { width: w, height: 900 } });
  await ctx.addCookies([{ name: 'ss_entry', value: '1', url: 'http://localhost:8888' }]);
  const p = await ctx.newPage(); const errs = [];
  p.on('console', m => m.type() === 'error' && errs.push(m.text())); p.on('pageerror', e => errs.push(String(e)));
  await p.goto('http://localhost:8888' + path, { waitUntil: 'networkidle' });
  await p.evaluate(async () => { document.querySelectorAll('img').forEach(i => i.loading = 'eager'); await new Promise(r => setTimeout(r, 400)); });
  console.log(w, await p.evaluate(() => ({ sw: document.documentElement.scrollWidth, cw: document.documentElement.clientWidth, broken: [...document.images].filter(i => i.complete && !i.naturalWidth).map(i => i.src) })), errs.slice(0, 4));
  await p.screenshot({ path: `${out}-${w}.png`, fullPage: true });
  await ctx.close();
}
await b.close();
