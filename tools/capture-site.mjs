// Usage: node tools/capture-site.mjs <slug> <url> [extra-path ...]
// Captures layout and computed styles only. Stores no body copy, images or product content in JSON.
import { chromium } from 'playwright';
import fs from 'node:fs/promises';

const [slug, url, ...extra] = process.argv.slice(2);
const UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';
const outDir = `research/screens/${slug}`;
await fs.mkdir(outDir, { recursive: true });

const extract = () => {
  const P = ['fontFamily','fontSize','fontWeight','lineHeight','letterSpacing','textTransform','color','backgroundColor','borderRadius','borderTopWidth','borderTopColor','paddingTop','paddingBottom','paddingLeft','paddingRight','gap','columnGap','rowGap','maxWidth','width','boxShadow'];
  const style = (el) => { if (!el) return null; const c = getComputedStyle(el); const o = {}; P.forEach(p => o[p] = c[p]); return o; };
  const vis = (el) => { const r = el.getBoundingClientRect(); return r.width > 0 && r.height > 0; };
  const first = (sels) => { for (const s of sels) { for (const e of document.querySelectorAll(s)) if (vis(e)) return e; } return null; };
  const card = first(['li.product','.product-card','.product-item','[class*="product-card"]','[class*="ProductCard"]','.card-product','[class*="product"] article']);
  const btn = first(['a.button','button.button','.btn-primary','.btn','[class*="button--primary"]','main a[class*="btn"]','main a[class*="button"]','main button']);
  const navRoot = first(['header nav','nav','[role="navigation"]']);
  const navLink = navRoot?.querySelector('a');
  const container = first(['.container','[class*="container"]','[class*="wrapper"]','main > * > *']);
  const roots = [...(document.querySelector('main')?.children?.length > 2 ? document.querySelector('main').children : document.body.children)].filter(vis);
  const flat = roots.length <= 3 ? roots.flatMap(r => [...r.children].filter(vis)) : roots;
  const sections = flat.map((el, i) => {
    const r = el.getBoundingClientRect(), c = getComputedStyle(el);
    const inner = el.querySelector('ul,div[class*="grid"],[class*="grid"]');
    return {
      index: i, tag: el.tagName.toLowerCase(),
      top: Math.round(r.top + scrollY), height: Math.round(r.height),
      paddingTop: c.paddingTop, paddingBottom: c.paddingBottom, backgroundColor: c.backgroundColor,
      counts: { headings: el.querySelectorAll('h1,h2,h3,h4').length, images: el.querySelectorAll('img,picture,svg').length, links: el.querySelectorAll('a').length, buttons: el.querySelectorAll('button,.button,.btn').length, forms: el.querySelectorAll('form').length, details: el.querySelectorAll('details,[class*="accordion"]').length },
      gridColumns: inner ? getComputedStyle(inner).gridTemplateColumns : null,
      role: null,
    };
  });
  const nav = navRoot ? { topLevelItems: navRoot.querySelectorAll(':scope > ul > li, :scope > a, :scope > div > a').length, totalLinks: navRoot.querySelectorAll('a').length, hasSubmenus: !!navRoot.querySelector('ul ul, [class*="mega"], [class*="dropdown"], [class*="submenu"]') } : null;
  return { title_present: !!document.title, h1: style(first(['h1'])), h2: style(first(['h2'])), body: style(document.body), navLink: style(navLink), primaryButton: style(btn), productCard: style(card), container: style(container), nav, sections, docHeight: document.documentElement.scrollHeight };
};

const browser = await chromium.launch();
const result = { site: url, capturedAt: new Date().toISOString(), pages: {} };
for (const path of ['', ...extra]) {
  const target = new URL(path || '/', url).href;
  const key = path || 'home';
  result.pages[key] = {};
  for (const w of [390, 1440]) {
    const ctx = await browser.newContext({ viewport: { width: w, height: w === 390 ? 844 : 900 }, userAgent: UA });
    const page = await ctx.newPage();
    try {
      const res = await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 45000 });
      await page.waitForTimeout(3500);
      // Scroll through once so lazy content loads.
      await page.evaluate(async () => { for (let y = 0; y < document.documentElement.scrollHeight; y += 600) { scrollTo(0, y); await new Promise(r => setTimeout(r, 120)); } scrollTo(0, 0); });
      await page.waitForTimeout(800);
      const name = `${key.replace(/[^a-z0-9]+/gi, '-')}-${w}.png`;
      await page.screenshot({ path: `${outDir}/${name}`, fullPage: true });
      result.pages[key][w] = { status: res?.status(), screenshot: `${outDir}/${name}`, ...(await page.evaluate(extract)) };
    } catch (e) { result.pages[key][w] = { error: String(e).slice(0, 300) }; }
    await ctx.close();
  }
}
await browser.close();
await fs.writeFile(`research/${slug}.json`, JSON.stringify(result, null, 2));
console.log('wrote', `research/${slug}.json`);
