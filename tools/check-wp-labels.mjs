// Fetches each generated label from the running site and confirms its QR decodes. Uses the sample product list.
import sharp from 'sharp';
import jsQR from 'jsqr';
import { execSync } from 'node:child_process';
const ids = execSync(`npx wp-env run cli wp post list --post_type=product --field=ID --format=csv 2>/dev/null`).toString().split(/\s+/).filter(x => /^\d+$/.test(x));
let ok = 0;
for (const id of ids) {
  const url = `http://localhost:8888/wp-content/uploads/sharp-science/${id}/label.svg`;
  const svg = (await (await fetch(url)).text()).replace(/width="[\d.]+mm" height="[\d.]+mm"/, 'width="1500" height="660"');
  const { data, info } = await sharp(Buffer.from(svg)).ensureAlpha().raw().toBuffer({ resolveWithObject: true });
  const r = jsQR(new Uint8ClampedArray(data), info.width, info.height);
  if (r?.data?.includes('/certificates/?batch=')) ok++; else console.log('FAIL', id, r?.data);
  if (ids[0] === id) console.log('sample', r?.data);
}
console.log(`PHP label QR decode: ${ok}/${ids.length}`);
