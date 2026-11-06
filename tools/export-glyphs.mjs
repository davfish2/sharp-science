// Exports glyph outlines and the logo mark as JSON so the plugin can outline text in pure PHP.
import fs from 'node:fs';
import { font } from './text-path.mjs';
import { mark } from './logo-geometry.mjs';
const chars = [...Array(95).keys()].map(i => String.fromCharCode(32 + i)).join('') + '–—µ±×·°';
const out = { fonts: {}, mark: mark() };
for (const n of ['Geist-400', 'Geist-500', 'Geist-600', 'GeistMono-400', 'GeistMono-500']) {
  const f = font(n), g = {};
  for (const ch of chars) {
    const gl = f.charToGlyph(ch), c = [];
    for (const k of gl.getPath(0, 0, f.unitsPerEm).commands) {
      if (k.type === 'Z') c.push(['Z']);
      else if (k.type === 'Q') c.push(['Q', k.x1, k.y1, k.x, k.y]);
      else if (k.type === 'C') c.push(['C', k.x1, k.y1, k.x2, k.y2, k.x, k.y]);
      else c.push([k.type, k.x, k.y]);
    }
    g[ch] = { a: gl.advanceWidth, c };
  }
  out.fonts[n] = { upm: f.unitsPerEm, g };
}
const r = o => JSON.stringify(o, (k, v) => typeof v === 'number' ? +v.toFixed(1) : v);
fs.writeFileSync('wp-content/plugins/sharp-science-core/assets/glyphs.json', r(out));
