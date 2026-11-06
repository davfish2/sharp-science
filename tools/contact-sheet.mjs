// Usage: node tools/contact-sheet.mjs <out.png>. Tiles every render for visual review.
import sharp from 'sharp';
import fs from 'node:fs';
const d = 'brand/renders/', files = fs.readdirSync(d).filter(f => f.endsWith('.webp')).sort();
const t = 380, cols = 4;
const comp = await Promise.all(files.map(async (f, i) => ({ input: await sharp(d + f).resize(t, t).png().toBuffer(), left: (i % cols) * (t + 6), top: Math.floor(i / cols) * (t + 6) })));
await sharp({ create: { width: cols * (t + 6), height: Math.ceil(files.length / cols) * (t + 6), channels: 3, background: '#fff' } }).composite(comp).png().toFile(process.argv[2]);
