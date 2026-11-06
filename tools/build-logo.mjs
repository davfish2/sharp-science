import fs from 'node:fs';
import { execFileSync } from 'node:child_process';
import sharp from 'sharp';
import { mark } from './logo-geometry.mjs';
import { text } from './text-path.mjs';

const C = { ink: '#16181D', brass: '#A8844E', paper: '#F6F4EF', white: '#FFFFFF' };
const M = mark(), MF = mark({ w: 12.5, gap: 2.4, band: 3.4, ch: 5, term: 10, hx: 23 });
const markG = (m, ink, x = 0, y = 0, s = 1) => `<g transform="translate(${x} ${y}) scale(${s})"><path fill="${ink}" d="${m.strokes}"/><path fill="${C.brass}" d="${m.cut}"/></g>`;
const svg = (w, h, body, title) => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${+w.toFixed(2)} ${+h.toFixed(2)}" role="img" aria-label="${title}"><title>${title}</title>${body}</svg>\n`;

// SHARP (600) and SCIENCE (500) set as one line, tracking 0.2em.
function wordLine(size, x, y, anchor = 'start') {
  const T = 0.2, space = size * 0.62;
  const a = text('SHARP', { f: 'Geist-600', size, tracking: T }), b = text('SCIENCE', { f: 'Geist-500', size, tracking: T });
  const total = a.width + space + b.width, x0 = anchor === 'middle' ? x - total / 2 : x;
  return { width: total, d: text('SHARP', { f: 'Geist-600', size, x: x0, y, tracking: T }).d + text('SCIENCE', { f: 'Geist-500', size, x: x0 + a.width + space, y, tracking: T }).d };
}

const out = {};
for (const [mode, ink] of [['light', C.ink], ['dark', C.paper]]) {
  out[`mark-${mode}`] = svg(64, 64, markG(M, ink), 'Sharp Science');
  const size = 22, w = wordLine(size, 80, 32 + size * 0.355);
  out[`horizontal-${mode}`] = svg(80 + w.width + 2, 64, markG(M, ink) + `<path fill="${ink}" d="${w.d}"/>`, 'Sharp Science');
  const s2 = 15, probe = wordLine(s2, 0, 0), W = probe.width + 4, line = wordLine(s2, W / 2, 112, 'middle');
  out[`stacked-${mode}`] = svg(W, 120, markG(M, ink, W / 2 - 40, 0, 80 / 64) + `<path fill="${ink}" d="${line.d}"/>`, 'Sharp Science');
}
for (const [k, v] of Object.entries(out)) fs.writeFileSync(`brand/logo/sharp-science-${k}.svg`, v);

// Favicons use the sturdier small-size cut on a white tile so the mark holds on dark tabs.
const fav = svg(64, 64, `<rect width="64" height="64" rx="8" fill="${C.white}"/>` + markG(MF, C.ink, 6.4, 6.4, 0.8), 'Sharp Science');
fs.writeFileSync('brand/favicon/favicon.svg', fav);
for (const s of [16, 32, 48, 180, 192, 512]) await sharp(Buffer.from(fav), { density: 72 * Math.ceil(s / 64) * 4 }).resize(s, s).png().toFile(`brand/favicon/favicon-${s}.png`);
// ICO with embedded PNGs (16, 32, 48).
const pngs = [16, 32, 48].map(s => [s, fs.readFileSync(`brand/favicon/favicon-${s}.png`)]);
const head = Buffer.alloc(6); head.writeUInt16LE(1, 2); head.writeUInt16LE(pngs.length, 4);
let off = 6 + 16 * pngs.length; const dir = [];
for (const [s, b] of pngs) { const e = Buffer.alloc(16); e[0] = s; e[1] = s; e.writeUInt16LE(1, 4); e.writeUInt16LE(32, 6); e.writeUInt32LE(b.length, 8); e.writeUInt32LE(off, 12); off += b.length; dir.push(e); }
fs.writeFileSync('brand/favicon/favicon.ico', Buffer.concat([head, ...dir, ...pngs.map(p => p[1])]));

// Social image, 1200 x 630.
const line = text('Laboratory glassware, vials and instruments.', { f: 'Geist-400', size: 30, x: 96, y: 500 });
const wl = wordLine(34, 214, 214 + 0);
const social = `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630"><rect width="1200" height="630" fill="${C.paper}"/>${markG(M, C.ink, 83.2, 150, 1.6)}<path fill="${C.ink}" d="${wl.d}"/><rect x="96" y="444" width="1008" height="1" fill="#D9D3C8"/><path fill="#5E646E" d="${line.d}"/><rect x="0" y="622" width="1200" height="8" fill="${C.brass}"/></svg>`;
await sharp(Buffer.from(social)).png().toFile('brand/social/sharp-science-social-1200x630.png');
console.log('logo set built');
