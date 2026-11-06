// Rasterises every label and confirms its QR decodes to the correct batch URL.
import fs from 'node:fs';
import sharp from 'sharp';
import jsQR from 'jsqr';
import { loadProducts } from './products.mjs';
import { QR_BASE } from './label-template.mjs';
let pass = 0; const ps = loadProducts();
for (const p of ps) {
  const svg = fs.readFileSync(`brand/labels/svg/${p.catalogue_no}.svg`, 'utf8').replace('width="50mm" height="22mm"', 'width="1500" height="660"');
  const { data, info } = await sharp(Buffer.from(svg)).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
  const r = jsQR(new Uint8ClampedArray(data), info.width, info.height);
  const want = `${QR_BASE}/certificates/?batch=${p.batch}`;
  if (r?.data === want) pass++; else console.log('FAIL', p.catalogue_no, r?.data);
}
console.log(`QR decode: ${pass}/${ps.length}`);
