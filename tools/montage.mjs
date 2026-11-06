// Usage: node tools/montage.mjs <out> <cols> <width> <files...>
import sharp from 'sharp';
const [out, cols, w, ...fs] = process.argv.slice(2); const W = +w, C = +cols;
const imgs = await Promise.all(fs.map(async f => { const b = await sharp(f).resize(W).png().toBuffer(); return { b, h: (await sharp(b).metadata()).height }; }));
const H = Math.max(...imgs.map(i => i.h));
await sharp({ create: { width: C * (W + 8), height: Math.ceil(fs.length / C) * (H + 8), channels: 3, background: '#888' } }).composite(imgs.map((i, n) => ({ input: i.b, left: (n % C) * (W + 8), top: Math.floor(n / C) * (H + 8) }))).png().toFile(out);
