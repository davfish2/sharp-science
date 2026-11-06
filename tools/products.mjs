import fs from 'node:fs';
// Minimal CSV reader that handles quoted fields.
export function loadProducts(path = 'data/products.csv') {
  const rows = fs.readFileSync(path, 'utf8').trim().split('\n').map(l => [...l.matchAll(/("([^"]*)"|[^,]*)(,|$)/g)].slice(0, -1).map(m => m[2] ?? m[1]));
  const [head, ...data] = rows;
  return data.map((r, i) => ({ ...Object.fromEntries(head.map((h, j) => [h, r[j]])), batch: `B26-${1001 + i * 7}` }));
}
export function renderType(p) {
  const n = p.name.toLowerCase();
  if (n.includes('amber')) return 'amber';
  if (n.includes('vial') && !n.includes('rack')) return 'vial';
  if (n.includes('beaker')) return 'beaker';
  if (n.includes('erlenmeyer')) return 'erlenmeyer';
  if (n.includes('volumetric')) return 'volumetric';
  if (n.includes('cylinder')) return 'cylinder';
  if (n.includes('micropipette')) return 'pipette';
  if (n.includes('solution')) return 'bottle';
  return 'box';
}
