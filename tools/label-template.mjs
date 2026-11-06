// Parameterised product label. All measurements are millimetres. W and H are the trim size.
import QRCode from 'qrcode';
import { mark } from './logo-geometry.mjs';
import { text } from './text-path.mjs';

const INK = '#16181D', BRASS = '#A8844E';
export const QR_BASE = process.env.QR_BASE || 'https://sharpscience.example';

function qrPath(url, x, y, size) {
  const q = QRCode.create(url, { errorCorrectionLevel: 'M' }), n = q.modules.size, m = size / n;
  let d = '';
  for (let r = 0; r < n; r++) for (let c = 0; c < n; c++) {
    if (!q.modules.get(r, c)) continue;
    let run = 1; while (c + run < n && q.modules.get(r, c + run)) run++;
    d += `M${(x + c * m).toFixed(3)} ${(y + r * m).toFixed(3)}h${(run * m).toFixed(3)}v${m.toFixed(3)}h-${(run * m).toFixed(3)}z`;
    c += run - 1;
  }
  return d;
}

function wrap(str, f, size, max) {
  const lines = []; let cur = '';
  for (const w of str.split(' ')) {
    const t = cur ? `${cur} ${w}` : w;
    if (text(t, { f, size }).width <= max || !cur) cur = t; else { lines.push(cur); cur = w; }
  }
  return [...lines, cur];
}

// Inner artwork. bleed extends the band past the trim edge for print.
export function labelBody(p, { W = 50, H = 22, bleed = 0 } = {}) {
  const k = H / 22, leftW = 8.5 * k, rightW = 12.6 * k, bandW = 2, pad = 1.5 * k;
  const bandX = W - rightW - bandW, cx = leftW + 1.6 * k, cw = bandX - cx - 1.4 * k;
  const specs = p.spec.split(',').map(s => s.trim());
  // The spec is boxed, so the printed name drops a repeated size from its end.
  const name = p.name.replace(new RegExp(`\\s*${specs[0].replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}$`), '').trim() || p.name;

  let size = 2.7 * k, lines = wrap(name, 'Geist-500', size, cw);
  while (lines.length > 2 || lines.some(l => text(l, { f: 'Geist-500', size }).width > cw)) { size -= 0.1 * k; lines = wrap(name, 'Geist-500', size, cw); }

  let o = `<rect x="${-bleed}" y="${-bleed}" width="${W + 2 * bleed}" height="${H + 2 * bleed}" fill="#FFFFFF"/>`;
  const m = mark(), ms = 5.4 * k / 64, mx = (leftW - 5.4 * k) / 2;
  o += `<g transform="translate(${mx} ${pad}) scale(${ms})"><path fill="${INK}" d="${m.strokes}"/><path fill="${BRASS}" d="${m.cut}"/></g>`;
  const vs = 1.14 * k, word = text('SHARP', { f: 'Geist-600', size: vs, tracking: .2 }).width + vs * .6;
  const vy = H - pad, vx = leftW / 2 + vs * .36;
  o += `<g transform="translate(${vx} ${vy}) rotate(-90)"><path fill="${INK}" d="${text('SHARP', { f: 'Geist-600', size: vs, tracking: .2 }).d}${text('SCIENCE', { f: 'Geist-500', size: vs, x: word, tracking: .2 }).d}"/></g>`;
  o += `<rect x="${leftW}" y="${pad}" width="0.12" height="${H - 2 * pad}" fill="${BRASS}"/>`;

  const lh = size * 1.18; let y = 3.1 * k + size * .8;
  for (const l of lines) { o += `<path fill="${INK}" d="${text(l, { f: 'Geist-500', size, x: cx, y }).d}"/>`; y += lh; }

  // Shrink the spec row until every box fits, so no spec is ever dropped.
  const rowW = z => specs.reduce((a, s) => a + text(s, { f: 'GeistMono-500', size: z }).width + 1.7 * k * z / (1.75 * k) + .9 * k, -.9 * k);
  let bs = 1.75 * k; while (rowW(bs) > bandX - cx - .6 * k && bs > 1 * k) bs -= .03 * k;
  const bh = 3.5 * k, by = H - pad - bh - .2 * k, bp = .85 * k * bs / (1.75 * k); let bx = cx;
  for (const s of specs) {
    const t = text(s, { f: 'GeistMono-500', size: bs }), bw = t.width + 2 * bp;
    o += `<rect x="${bx}" y="${by}" width="${bw}" height="${bh}" fill="none" stroke="${INK}" stroke-width="0.14"/>`;
    o += `<path fill="${INK}" d="${text(s, { f: 'GeistMono-500', size: bs, x: bx + bp, y: by + bh / 2 + bs * .35 }).d}"/>`;
    bx += bw + .9 * k;
  }

  o += `<rect x="${bandX}" y="${-bleed}" width="${bandW}" height="${H + 2 * bleed}" fill="${p.label_band_colour}"/>`;

  const rx = bandX + bandW + 1.15 * k, qs = 9.6 * k;
  o += `<path fill="${INK}" d="${qrPath(`${QR_BASE}/certificates/?batch=${p.batch}`, rx, 1.05 * k, qs)}"/>`;
  const ty = 1.05 * k + qs;
  o += `<path fill="${INK}" d="${text(p.catalogue_no, { f: 'GeistMono-500', size: 1.5 * k, x: rx, y: ty + 2.5 * k }).d}"/>`;
  o += `<path fill="${INK}" d="${text(`Batch ${p.batch}`, { f: 'GeistMono-400', size: 1.16 * k, x: rx, y: ty + 4.5 * k }).d}"/>`;
  o += `<path fill="${INK}" d="${text('For laboratory use', { f: 'Geist-400', size: 1.06 * k, x: rx, y: H - pad + .35 * k }).d}"/>`;
  return o;
}

export const labelSVG = (p, o = {}) => { const { W = 50, H = 22 } = o; return `<svg xmlns="http://www.w3.org/2000/svg" width="${W}mm" height="${H}mm" viewBox="0 0 ${W} ${H}" role="img" aria-label="${p.name.replace(/&/g, '&amp;')} label">${labelBody(p, o)}</svg>\n`; };
