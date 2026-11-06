import fs from 'node:fs';
const t = JSON.parse(fs.readFileSync('design/tokens.json'));
const c = { ...t.colour, ...t.band };
const lin = v => (v /= 255) <= .03928 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4;
const L = h => { const n = parseInt(h.slice(1), 16); return .2126 * lin(n >> 16) + .7152 * lin(n >> 8 & 255) + .0722 * lin(n & 255); };
const ratio = (a, b) => { const [x, y] = [L(a), L(b)].sort((p, q) => q - p); return (x + .05) / (y + .05); };
// [foreground, background, use, required ratio]
const pairs = [
  ['graphite','white','Body text',4.5],['graphite','porcelain','Body text',4.5],['graphite','bone','Body text',4.5],['graphite','glacier','Body text',4.5],
  ['slate','white','Secondary text',4.5],['slate','porcelain','Secondary text',4.5],['slate','bone','Secondary text',4.5],['slate','glacier','Secondary text',4.5],
  ['white','graphite','Footer and button text',4.5],['porcelain','graphite','Footer and button text',4.5],['hairline','graphite','Footer secondary text',4.5],
  ['brass','graphite','Accent text on dark',4.5],['brass','white','Large text only',3],['brass','porcelain','Large text only',3],
  ['signal','white','In-stock and success text',4.5],['signal','porcelain','In-stock and success text',4.5],['white','signal','Text on success fill',4.5],
  ['graphite','vials-storage','Text on band',4.5],['graphite','volumetric-glassware','Text on band',4.5],['graphite','beakers-flasks','Text on band',4.5],['graphite','pipetting','Text on band',4.5],['graphite','instruments','Text on band',4.5],['graphite','lab-essentials','Text on band',4.5],
  ['hairline','white','Decorative rule (exempt)',0],['slate','white','Input border (non-text)',3],
];
const rows = pairs.map(([f,b,use,req]) => { const r = ratio(c[f], c[b]); return { fg:f, bg:b, fgHex:c[f], bgHex:c[b], use, required:req, ratio:+r.toFixed(2), pass:r>=req }; });
fs.writeFileSync('design/contrast.json', JSON.stringify(rows, null, 2));
console.table(rows.map(({fg,bg,use,required,ratio,pass})=>({fg,bg,use,required,ratio,pass})));
