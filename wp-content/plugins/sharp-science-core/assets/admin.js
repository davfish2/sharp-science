/* Converts generated SVG renders to WebP in the admin browser and uploads them. */
(() => {
  const A = window.SS_ADMIN;
  if (!A) return;
  const status = (t) => { const el = document.getElementById('ss-status'); if (el) el.textContent = t; };

  const convert = ({ id, url }) => new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => {
      const c = document.createElement('canvas');
      c.width = c.height = 1600;
      c.getContext('2d').drawImage(img, 0, 0, 1600, 1600);
      c.toBlob(async (blob) => {
        const res = await fetch(`${A.rest}render/${id}`, { method: 'POST', headers: { 'X-WP-Nonce': A.nonce, 'Content-Type': blob.type }, body: blob, credentials: 'same-origin' });
        res.ok ? resolve() : reject(new Error(`Upload failed for ${id}`));
      }, 'image/webp', 0.84);
    };
    img.onerror = () => reject(new Error(`Could not load ${url}`));
    img.src = `${url}?v=${Date.now()}`;
  });

  const run = async () => {
    const list = A.pending;
    if (!list.length) return;
    let n = 0, failed = 0;
    for (const item of list) {
      status(`Preparing product image ${n + 1} of ${list.length}…`);
      try { await convert(item); } catch (e) { failed++; console.error(e); }
      n++;
    }
    status(failed ? `${failed} image(s) could not be prepared. Reload this page to try again.` : (list.length > 1 ? `${n} product images updated.` : 'Label and image are up to date.'));
    document.documentElement.dataset.ssRendered = String(n - failed);
  };
  run();
})();
