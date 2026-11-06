// Generates one SVG per product and a print-ready PDF with 3 mm bleed and crop marks.
import fs from 'node:fs';
import { chromium } from 'playwright';
import { PDFDocument, PDFName } from 'pdf-lib';
import { loadProducts } from './products.mjs';
import { labelBody, labelSVG } from './label-template.mjs';

const W = +(process.env.LABEL_W || 50), H = +(process.env.LABEL_H || 22), BLEED = 3, SLUG = 5;
const M = BLEED + SLUG, PW = W + 2 * M, PH = H + 2 * M;
const products = loadProducts();
for (const p of products) fs.writeFileSync(`brand/labels/svg/${p.catalogue_no}.svg`, labelSVG(p, { W, H }));

const crop = () => {
  const L = 3.5, g = BLEED + .5, s = 'stroke="#000" stroke-width="0.09"'; let o = '';
  for (const x of [0, W]) for (const [a, b] of [[-g - L, -g], [H + g, H + g + L]]) o += `<line x1="${x}" x2="${x}" y1="${a}" y2="${b}" ${s}/>`;
  for (const y of [0, H]) for (const [a, b] of [[-g - L, -g], [W + g, W + g + L]]) o += `<line y1="${y}" y2="${y}" x1="${a}" x2="${b}" ${s}/>`;
  return o;
};
const page = p => `<section><svg xmlns="http://www.w3.org/2000/svg" width="${PW}mm" height="${PH}mm" viewBox="${-M} ${-M} ${PW} ${PH}"><defs><clipPath id="b"><rect x="${-BLEED}" y="${-BLEED}" width="${W + 2 * BLEED}" height="${H + 2 * BLEED}"/></clipPath></defs><g clip-path="url(#b)">${labelBody(p, { W, H, bleed: BLEED })}</g>${crop()}</svg></section>`;
const html = `<!doctype html><style>@page{size:${PW}mm ${PH}mm;margin:0}html,body{margin:0}section{width:${PW}mm;height:${PH}mm;page-break-after:always;overflow:hidden}svg{display:block}</style>${products.map(page).join('')}`;

const b = await chromium.launch(), pg = await b.newPage();
await pg.setContent(html);
const raw = await pg.pdf({ width: `${PW}mm`, height: `${PH}mm`, printBackground: true, preferCSSPageSize: true });
await b.close();

// Record trim and bleed boxes so a printer's imposition software reads them directly.
const doc = await PDFDocument.load(raw), mm = 72 / 25.4;
doc.setTitle('Sharp Science product labels'); doc.setProducer('Sharp Science label generator');
for (const pgx of doc.getPages()) {
  const h = pgx.getHeight();
  pgx.setTrimBox(M * mm, h - (M + H) * mm, W * mm, H * mm);
  pgx.setBleedBox((M - BLEED) * mm, h - (M + H + BLEED) * mm, (W + 2 * BLEED) * mm, (H + 2 * BLEED) * mm);
}
fs.writeFileSync('brand/labels/print/sharp-science-labels-print.pdf', await doc.save());
console.log(`labels: ${products.length} SVG, PDF pages ${doc.getPageCount()}`);
