import opentype from 'opentype.js';
import fs from 'node:fs';
const cache = {};
export const font = n => cache[n] ??= opentype.parse(fs.readFileSync(`brand/fonts/${n}.ttf`).buffer.slice(0));
// Returns outlined text. tracking is in em. anchor: start | middle | end.
export function text(str, { f, size, x = 0, y = 0, tracking = 0, anchor = 'start' }) {
  const ft = font(f), sc = size / ft.unitsPerEm;
  const glyphs = ft.stringToGlyphs(str);
  let adv = 0; const pos = [];
  glyphs.forEach((g, i) => {
    pos.push(adv);
    adv += g.advanceWidth * sc + (i < glyphs.length - 1 ? tracking * size : 0);
    if (i < glyphs.length - 1) adv += ft.getKerningValue(g, glyphs[i + 1]) * sc;
  });
  const x0 = anchor === 'middle' ? x - adv / 2 : anchor === 'end' ? x - adv : x;
  const d = glyphs.map((g, i) => g.getPath(x0 + pos[i], y, size).toPathData(3)).join('');
  return { d, width: adv };
}
