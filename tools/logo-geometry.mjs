// Mark geometry on a 64-unit grid. Two strokes split by one 30-degree brass cut through the centre.
const T = Math.tan(Math.PI / 6);
const lineY = (x, off = 0) => 32 - T * (x - 32) + off; // rising to the right, 30 degrees from horizontal

// Clip a polygon to one side of the diagonal (Sutherland-Hodgman, single half-plane).
function clip(poly, keepAbove, off) {
  const inside = ([x, y]) => keepAbove ? y <= lineY(x, off) : y >= lineY(x, off);
  const out = [];
  poly.forEach((p, i) => {
    const q = poly[(i + 1) % poly.length], a = inside(p), b = inside(q);
    if (a) out.push(p);
    if (a !== b) {
      const f = ([x, y]) => y - lineY(x, off), t = f(p) / (f(p) - f(q));
      out.push([p[0] + t * (q[0] - p[0]), p[1] + t * (q[1] - p[1])]);
    }
  });
  return out;
}

export function mark({ w = 11, gap = 2, band = 2.6, L = 8, R = 56, top = 4, bot = 60, ch = 5, hx = 24, term = 9 } = {}) {
  const midT = 32 - w / 2, midB = 32 + w / 2;
  const dx = ch, dy = ch * T;           // chamfers follow the 30-degree angle
  const S = [
    [L + dx, top], [R, top], [R, top + w - term * T], [R - term, top + w],
    [L + w, top + w], [L + w, midT], [R - dx, midT], [R, midT + dy], [R, bot - dy], [R - dx, bot],
    [L, bot], [L, bot - w + term * T], [L + term, bot - w],
    [R - w, bot - w], [R - w, midB], [L + dx, midB], [L, midB - dy], [L, top + dy],
  ];
  const upper = clip(S, true, -(band / 2 + gap));
  const lower = clip(S, false, band / 2 + gap);
  const cx = 32;
  const cut = [[cx - hx, lineY(cx - hx, -band / 2)], [cx + hx, lineY(cx + hx, -band / 2)], [cx + hx, lineY(cx + hx, band / 2)], [cx - hx, lineY(cx - hx, band / 2)]];
  const d = p => 'M' + p.map(([x, y]) => `${+x.toFixed(2)} ${+y.toFixed(2)}`).join('L') + 'Z';
  return { strokes: d(upper) + d(lower), cut: d(cut) };
}
