// 2.5D product renders: SVG scene -> Chromium -> 1600 x 1600 WebP. Each vessel wears its own label.
import fs from 'node:fs';
import { chromium } from 'playwright';
import sharp from 'sharp';
import { loadProducts, renderType } from './products.mjs';
import { labelBody } from './label-template.mjs';
import { text } from './text-path.mjs';

const S = 1600, CX = 800, FLOOR = 1370;
const defs = `
<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ECE7DE"/><stop offset="1" stop-color="#DCE6E8"/></linearGradient>
<linearGradient id="glass" x1="0" x2="1"><stop offset="0" stop-color="#7F949A" stop-opacity=".62"/><stop offset=".07" stop-color="#C9D6D9" stop-opacity=".42"/><stop offset=".22" stop-color="#FFFFFF" stop-opacity=".30"/><stop offset=".5" stop-color="#FFFFFF" stop-opacity=".12"/><stop offset=".8" stop-color="#FFFFFF" stop-opacity=".24"/><stop offset=".94" stop-color="#B7C7CB" stop-opacity=".44"/><stop offset="1" stop-color="#71878E" stop-opacity=".66"/></linearGradient>
<linearGradient id="amber" x1="0" x2="1"><stop offset="0" stop-color="#3F2410" stop-opacity=".94"/><stop offset=".12" stop-color="#7A4A1E" stop-opacity=".88"/><stop offset=".3" stop-color="#A96E31" stop-opacity=".78"/><stop offset=".55" stop-color="#8C5825" stop-opacity=".82"/><stop offset=".88" stop-color="#6A3F19" stop-opacity=".9"/><stop offset="1" stop-color="#351D0C" stop-opacity=".96"/></linearGradient>
<linearGradient id="metal" x1="0" x2="1"><stop offset="0" stop-color="#0E1013"/><stop offset=".18" stop-color="#3A3E46"/><stop offset=".34" stop-color="#6A707A"/><stop offset=".5" stop-color="#2B2E35"/><stop offset=".85" stop-color="#16181D"/><stop offset="1" stop-color="#08090B"/></linearGradient>
<linearGradient id="poly" x1="0" x2="1"><stop offset="0" stop-color="#C7CBCC"/><stop offset=".16" stop-color="#F1F1EE"/><stop offset=".38" stop-color="#FFFFFF"/><stop offset=".75" stop-color="#ECECE8"/><stop offset="1" stop-color="#B8BDBF"/></linearGradient>
<linearGradient id="wrap" x1="0" x2="1"><stop offset="0" stop-color="#16181D" stop-opacity=".30"/><stop offset=".1" stop-color="#16181D" stop-opacity=".07"/><stop offset=".3" stop-color="#FFF" stop-opacity=".10"/><stop offset=".62" stop-color="#16181D" stop-opacity="0"/><stop offset=".92" stop-color="#16181D" stop-opacity=".09"/><stop offset="1" stop-color="#16181D" stop-opacity=".32"/></linearGradient>
<linearGradient id="face" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFFFFF"/><stop offset="1" stop-color="#EFEDE7"/></linearGradient>
<linearGradient id="side" x1="0" x2="1"><stop offset="0" stop-color="#D4D2CB"/><stop offset="1" stop-color="#BFBEB8"/></linearGradient>
<filter id="soft" x="-30%" y="-200%" width="160%" height="500%"><feGaussianBlur stdDeviation="34"/></filter>
<filter id="tight" x="-30%" y="-200%" width="160%" height="500%"><feGaussianBlur stdDeviation="9"/></filter>`;

const shadow = (w, x = CX) => `<ellipse cx="${x + 30}" cy="${FLOOR + 6}" rx="${w * .6}" ry="46" fill="#3B4A50" opacity=".20" filter="url(#soft)"/><ellipse cx="${x}" cy="${FLOOR}" rx="${w * .5}" ry="13" fill="#20282C" opacity=".36" filter="url(#tight)"/>`;
const hl = (x, y, w, h, o = .55) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${w / 2}" fill="#FFF" opacity="${o}"/>`;
const label = (p, x, y, w, { rotate = 0, wrap = true } = {}) => {
  const h = w * 22 / 50, inner = `<svg x="${x}" y="${y}" width="${w}" height="${h}" viewBox="0 0 50 22">${labelBody(p)}</svg>` + (wrap ? `<rect x="${x}" y="${y}" width="${w}" height="${h}" fill="url(#wrap)"/>` : '');
  return rotate ? `<g transform="rotate(${rotate} ${x + w / 2} ${y + h / 2})">${inner}</g>` : inner;
};
const grads = (x, y0, n, step, long = 5) => Array.from({ length: n }, (_, i) => `<rect x="${x}" y="${y0 + i * step}" width="${i % long ? 26 : 52}" height="3" fill="#16181D" opacity=".55"/>`).join('');

function vial(p, fill) {
  const ml = parseFloat(p.spec), H = { 2: 560, 5: 730, 10: 900, 20: 960 }[ml] ?? 900, W = 540, x = CX - W / 2, bot = FLOOR, top = bot - H, nw = 300, nx = CX - nw / 2, neck = top - 92, r = 44;
  return shadow(W) + `
  <path d="M${x} ${top + 92} C${x} ${top + 30} ${nx - 6} ${top + 34} ${nx} ${neck + 54} L${nx} ${neck} L${nx + nw} ${neck} L${nx + nw} ${neck + 54} C${nx + nw + 6} ${top + 34} ${x + W} ${top + 30} ${x + W} ${top + 92} L${x + W} ${bot - r} Q${x + W} ${bot} ${x + W - r} ${bot} L${x + r} ${bot} Q${x} ${bot} ${x} ${bot - r} Z" fill="url(#${fill})" stroke="#70858C" stroke-opacity=".5" stroke-width="3"/>
  <rect x="${x + 14}" y="${bot - 30}" width="${W - 28}" height="14" rx="7" fill="#FFF" opacity=".28"/>
  ${hl(x + 58, top + 120, 24, H - 210, fill === 'amber' ? .28 : .6)}${hl(x + W - 62, top + 150, 10, H - 260, .3)}
  <rect x="${nx - 26}" y="${neck - 52}" width="${nw + 52}" height="106" rx="10" fill="#A9AFB4"/><rect x="${nx - 26}" y="${neck - 52}" width="${nw + 52}" height="106" rx="10" fill="url(#wrap)"/>
  <rect x="${nx - 26}" y="${neck + 38}" width="${nw + 52}" height="16" rx="6" fill="#81878C"/>
  <path d="M${nx - 6} ${neck - 52} L${nx + 8} ${neck - 142} Q${nx + 10} ${neck - 154} ${nx + 24} ${neck - 154} L${nx + nw - 24} ${neck - 154} Q${nx + nw - 10} ${neck - 154} ${nx + nw - 8} ${neck - 142} L${nx + nw + 6} ${neck - 52} Z" fill="url(#metal)"/>
  ${label(p, x + 2, bot - Math.min(H * .62, 420), W - 4)}`;
}
function beaker(p) {
  const W = 780, H = 860, x = CX - W / 2, top = FLOOR - H;
  return shadow(W) + `
  <path d="M${x - 26} ${top} Q${x - 4} ${top + 2} ${x + 6} ${top + 34} L${x + 10} ${FLOOR - 50} Q${x + 12} ${FLOOR} ${x + 60} ${FLOOR} L${x + W - 60} ${FLOOR} Q${x + W - 12} ${FLOOR} ${x + W - 10} ${FLOOR - 50} L${x + W - 4} ${top + 8} Q${x + W + 2} ${top} ${x + W + 14} ${top} Z" fill="url(#glass)" stroke="#70858C" stroke-opacity=".55" stroke-width="3"/>
  <path d="M${x - 26} ${top} L${x + W + 14} ${top}" stroke="#FFF" stroke-opacity=".9" stroke-width="7" stroke-linecap="round"/>
  <rect x="${x + 22}" y="${FLOOR - 34}" width="${W - 44}" height="12" rx="6" fill="#FFF" opacity=".3"/>
  ${hl(x + 54, top + 70, 22, H - 170)}${grads(x + W - 118, top + 90, 11, 30)}
  ${label(p, CX - 330, FLOOR - 400, 640)}`;
}
function erlenmeyer(p) {
  const B = 860, nw = 240, top = FLOOR - 1090, sh = FLOOR - 800;
  return shadow(B) + `
  <path d="M${CX - nw / 2} ${top} L${CX - nw / 2} ${sh - 40} L${CX - B / 2 + 8} ${FLOOR - 70} Q${CX - B / 2 - 8} ${FLOOR} ${CX - B / 2 + 74} ${FLOOR} L${CX + B / 2 - 74} ${FLOOR} Q${CX + B / 2 + 8} ${FLOOR} ${CX + B / 2 - 8} ${FLOOR - 70} L${CX + nw / 2} ${sh - 40} L${CX + nw / 2} ${top} Z" fill="url(#glass)" stroke="#70858C" stroke-opacity=".55" stroke-width="3"/>
  <rect x="${CX - nw / 2 - 18}" y="${top - 16}" width="${nw + 36}" height="34" rx="14" fill="#FFF" opacity=".78" stroke="#70858C" stroke-opacity=".4" stroke-width="2"/>
  ${hl(CX - nw / 2 + 26, top + 50, 16, 280)}
  ${label(p, CX - 305, FLOOR - 340, 610)}`;
}
function volumetric(p) {
  const R = 400, cy = FLOOR - R + 24, nw = 128, top = 240;
  return shadow(R * 1.5) + `
  <path d="M${CX - nw / 2} ${top} L${CX - nw / 2} ${cy - R + 50} C${CX - nw / 2} ${cy - R + 150} ${CX - R} ${cy - 210} ${CX - R} ${cy + 60} C${CX - R} ${FLOOR - 40} ${CX - 280} ${FLOOR} ${CX - 200} ${FLOOR} L${CX + 200} ${FLOOR} C${CX + 280} ${FLOOR} ${CX + R} ${FLOOR - 40} ${CX + R} ${cy + 60} C${CX + R} ${cy - 210} ${CX + nw / 2} ${cy - R + 150} ${CX + nw / 2} ${cy - R + 50} L${CX + nw / 2} ${top} Z" fill="url(#glass)" stroke="#70858C" stroke-opacity=".55" stroke-width="3"/>
  <rect x="${CX - nw / 2}" y="${top + 230}" width="${nw}" height="5" fill="#16181D" opacity=".7"/>
  <rect x="${CX - nw / 2 - 16}" y="${top - 12}" width="${nw + 32}" height="30" rx="12" fill="#FFF" opacity=".8" stroke="#70858C" stroke-opacity=".4" stroke-width="2"/>
  <path d="M${CX - 62} ${top - 12} L${CX - 54} ${top - 86} L${CX + 54} ${top - 86} L${CX + 62} ${top - 12} Z" fill="url(#metal)"/>
  ${hl(CX - nw / 2 + 20, top + 40, 12, 300)}${hl(CX - R + 74, cy - 90, 20, 250, .5)}
  ${label(p, CX - 290, cy + 10, 580)}`;
}
function cylinder(p) {
  const W = 330, top = 190, x = CX - W / 2, bot = FLOOR - 56;
  return shadow(560) + `
  <path d="M${CX - 280} ${FLOOR} L${CX - 250} ${bot} L${CX + 250} ${bot} L${CX + 280} ${FLOOR} Z" fill="url(#glass)" stroke="#70858C" stroke-opacity=".55" stroke-width="3"/>
  <path d="M${x - 16} ${top} L${x} ${top + 24} L${x} ${bot} L${x + W} ${bot} L${x + W} ${top + 8} L${x + W + 6} ${top} Z" fill="url(#glass)" stroke="#70858C" stroke-opacity=".55" stroke-width="3"/>
  <path d="M${x - 16} ${top} L${x + W + 6} ${top}" stroke="#FFF" stroke-opacity=".9" stroke-width="7" stroke-linecap="round"/>
  ${hl(x + 28, top + 60, 16, bot - top - 130)}${grads(x + W - 78, top + 80, 34, 28)}
  ${label(p, CX - 300 - 30, 740 - 132, 600, { rotate: -90, wrap: false })}<rect x="${CX - 162}" y="${740 - 300}" width="264" height="600" fill="url(#wrap)"/>`;
}
function pipette(p) {
  // Air-displacement pipette: thumb plunger, ejector, finger hook, long tapered shaft, blunt disposable tip.
  const digits = text('1000', { f: 'GeistMono-500', size: 50, x: CX, y: 349, anchor: 'middle' }).d;
  return shadow(200, 880) + `<g transform="rotate(7 ${CX} ${FLOOR})">
  <rect x="${CX - 26}" y="118" width="52" height="110" fill="#A9AFB4"/><rect x="${CX - 26}" y="118" width="52" height="110" fill="url(#wrap)"/>
  <rect x="${CX - 62}" y="84" width="124" height="44" rx="8" fill="url(#metal)"/>
  <rect x="${CX + 76}" y="176" width="34" height="70" fill="#A9AFB4"/><rect x="${CX + 62}" y="154" width="62" height="30" rx="6" fill="url(#metal)"/>
  <path d="M${CX - 128} 262 Q${CX - 128} 228 ${CX - 94} 228 L${CX + 94} 228 Q${CX + 128} 228 ${CX + 128} 262 L${CX + 128} 930 Q${CX + 128} 960 ${CX + 100} 980 L${CX + 62} 1010 L${CX - 62} 1010 L${CX - 100} 980 Q${CX - 128} 960 ${CX - 128} 930 Z" fill="url(#poly)" stroke="#9EA5A7" stroke-opacity=".7" stroke-width="2"/>
  <path d="M${CX - 128} 400 C${CX - 214} 412 ${CX - 228} 470 ${CX - 208} 508 C${CX - 196} 528 ${CX - 170} 516 ${CX - 174} 492 C${CX - 176} 474 ${CX - 152} 464 ${CX - 128} 462 Z" fill="url(#metal)"/>
  <rect x="${CX - 92}" y="292" width="184" height="76" rx="4" fill="#16181D"/><path fill="#DCE6E8" d="${digits}"/>
  <path d="M${CX - 62} 1010 L${CX + 62} 1010 L${CX + 40} 1130 L${CX - 40} 1130 Z" fill="url(#metal)"/>
  <path d="M${CX - 26} 1130 L${CX + 26} 1130 L${CX + 15} 1230 L${CX - 15} 1230 Z" fill="#A9AFB4"/><path d="M${CX - 26} 1130 L${CX + 26} 1130 L${CX + 15} 1230 L${CX - 15} 1230 Z" fill="url(#wrap)"/>
  <path d="M${CX - 21} 1200 L${CX + 21} 1200 L${CX + 8} ${FLOOR - 4} Q${CX} ${FLOOR + 2} ${CX - 8} ${FLOOR - 4} Z" fill="#E7EEF0" fill-opacity=".85" stroke="#70858C" stroke-opacity=".7" stroke-width="2"/>
  ${label(p, CX - 245, 664 - 108, 490, { rotate: -90, wrap: false })}<rect x="${CX - 108}" y="${664 - 245 + 0}" width="216" height="490" fill="url(#wrap)"/>
  </g>`;
}
function bottle(p) {
  const W = 620, x = CX - W / 2, top = FLOOR - 900, nw = 240, r = 50;
  return shadow(W) + `
  <path d="M${CX - nw / 2} ${top - 110} L${CX - nw / 2} ${top - 46} C${CX - nw / 2} ${top + 10} ${x} ${top + 8} ${x} ${top + 110} L${x} ${FLOOR - r} Q${x} ${FLOOR} ${x + r} ${FLOOR} L${x + W - r} ${FLOOR} Q${x + W} ${FLOOR} ${x + W} ${FLOOR - r} L${x + W} ${top + 110} C${x + W} ${top + 8} ${CX + nw / 2} ${top + 10} ${CX + nw / 2} ${top - 46} L${CX + nw / 2} ${top - 110} Z" fill="url(#poly)" stroke="#9EA5A7" stroke-opacity=".6" stroke-width="2"/>
  <rect x="${CX - nw / 2 - 26}" y="${top - 250}" width="${nw + 52}" height="150" rx="10" fill="url(#metal)"/>
  ${Array.from({ length: 13 }, (_, i) => `<rect x="${CX - nw / 2 - 12 + i * 24}" y="${top - 238}" width="3" height="126" fill="#FFF" opacity=".10"/>`).join('')}
  ${label(p, x + 12, top + 330, W - 24)}`;
}
function box(p) {
  const n = p.name.toLowerCase(), inst = p.category === 'Instruments';
  const W = inst ? 860 : 780, H = inst ? 700 : 560, D = 170, x = CX - W / 2 - D / 2 + 10, y = FLOOR - H, dy = 86;
  return shadow(W + D, CX + 20) + `
  <path d="M${x} ${y} L${x + D} ${y - dy} L${x + W + D} ${y - dy} L${x + W} ${y} Z" fill="#FBFAF6" stroke="#CFCBC2" stroke-width="2"/>
  <path d="M${x + W} ${y} L${x + W + D} ${y - dy} L${x + W + D} ${FLOOR - dy} L${x + W} ${FLOOR} Z" fill="url(#side)"/>
  <rect x="${x}" y="${y}" width="${W}" height="${H}" fill="url(#face)" stroke="#CFCBC2" stroke-width="2"/>
  <rect x="${x}" y="${FLOOR - 26}" width="${W}" height="26" fill="${p.label_band_colour}"/>
  ${label(p, x + (W - 700) / 2, y + (H - 26 - 308) / 2, 700, { wrap: false })}<rect x="${x + (W - 700) / 2}" y="${y + (H - 26 - 308) / 2}" width="700" height="308" fill="none" stroke="#D9D3C8" stroke-width="2"/>`;
}
const draw = { vial: p => vial(p, 'glass'), amber: p => vial(p, 'amber'), beaker, erlenmeyer, volumetric, cylinder, pipette, bottle, box };
const scene = p => `<svg xmlns="http://www.w3.org/2000/svg" width="${S}" height="${S}" viewBox="0 0 ${S} ${S}"><defs>${defs}</defs><rect width="${S}" height="${S}" fill="url(#bg)"/>${draw[renderType(p)](p)}</svg>`;

const only = process.argv[2]?.split(',');
const b = await chromium.launch(), pg = await b.newPage({ viewport: { width: S, height: S } });
for (const p of loadProducts().filter(p => !only || only.includes(p.catalogue_no))) {
  await pg.setContent(`<!doctype html><body style="margin:0">${scene(p)}`);
  const png = await pg.screenshot({ type: 'png' });
  await sharp(png).webp({ quality: 84 }).toFile(`brand/renders/${p.catalogue_no}.webp`);
}
await b.close();
console.log('renders done');
