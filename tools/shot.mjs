// Usage: node tools/shot.mjs <file.html> <out-prefix>. Screenshots at 390 and 1440, and reports horizontal overflow.
import { chromium } from 'playwright';
import path from 'node:path';
const [file, out] = process.argv.slice(2);
const b = await chromium.launch();
for (const w of [390, 1440]) {
  const p = await b.newPage({ viewport: { width: w, height: 900 } });
  await p.goto('file://' + path.resolve(file)); await p.waitForLoadState('networkidle');
  await p.evaluate(async () => { for (const i of document.images) { i.loading = 'eager'; await i.decode().catch(() => {}); } await document.fonts.ready; });
  const o = await p.evaluate(() => ({ sw: document.documentElement.scrollWidth, cw: document.documentElement.clientWidth, broken: [...document.images].filter(i => !i.naturalWidth).length, fonts: [...document.fonts].filter(f => f.status === 'loaded').length }));
  console.log(w, o);
  await p.screenshot({ path: `${out}-${w}.png`, fullPage: true });
  await p.close();
}
await b.close();
