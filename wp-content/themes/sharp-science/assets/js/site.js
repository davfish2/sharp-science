/* Header, cart drawer, quick add, tabs. Vanilla, using the Store API. */
(() => {
  const S = window.SS, $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  let nonce = '';

  /* Header */
  const shop = $('.site-nav__shop'), mega = $('#mega'), menuBtn = $('[data-menu-toggle]'), nav = $('#site-nav'), searchBtn = $('[data-search-toggle]'), search = $('#site-search');
  const setMega = (o) => { mega.hidden = !o; shop.setAttribute('aria-expanded', o); };
  const setSearch = (o) => { search.hidden = !o; searchBtn.setAttribute('aria-expanded', o); if (o) $('input[type=search]', search).focus(); };
  shop.addEventListener('click', () => { setSearch(false); setMega(mega.hidden); });
  searchBtn.addEventListener('click', () => { setMega(false); setSearch(search.hidden); });
  menuBtn.addEventListener('click', () => { const o = nav.classList.toggle('is-open'); menuBtn.setAttribute('aria-expanded', o); });
  document.addEventListener('click', (e) => { if (!e.target.closest('.site-header')) { setMega(false); } });

  /* Store API */
  const api = async (path, opts = {}) => {
    const res = await fetch(S.store + path, { credentials: 'same-origin', ...opts, headers: { 'Content-Type': 'application/json', ...(nonce ? { Nonce: nonce } : {}), ...(opts.headers || {}) } });
    nonce = res.headers.get('Nonce') || nonce;
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || 'Something went wrong.');
    return data;
  };
  const money = (minor, c) => `${c.currency_prefix}${(minor / 10 ** c.currency_minor_unit).toLocaleString('en-AU', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
  const esc = (s) => { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

  const drawer = $('#cart-drawer'), scrim = $('[data-scrim]');
  let lastFocus = null;
  const openDrawer = () => { lastFocus = document.activeElement; drawer.hidden = false; scrim.hidden = false; document.documentElement.classList.add('has-drawer'); $('[data-cart-close]').focus(); };
  const closeDrawer = () => { drawer.hidden = true; scrim.hidden = true; document.documentElement.classList.remove('has-drawer'); if (lastFocus) lastFocus.focus(); };

  const paint = (cart) => {
    const t = cart.totals, count = cart.items_count, badge = $('[data-cart-count]');
    badge.textContent = count; badge.hidden = !count;
    $('[data-cart-open]').setAttribute('aria-label', count ? `Open cart, ${count} ${count === 1 ? 'item' : 'items'}` : 'Open cart');
    const minor = 10 ** t.currency_minor_unit;
    const value = (+t.total_items + +t.total_items_tax - +t.total_discount - +t.total_discount_tax) / minor;
    $('[data-cart-total]').textContent = money(+t.total_items + +t.total_items_tax, t);
    const left = Math.max(0, S.threshold - value), pct = S.threshold ? Math.min(100, Math.round(value / S.threshold * 100)) : 100;
    $$('[data-ship-bar]').forEach((bar) => {
      $('[data-ship-text]', bar).textContent = left > 0 ? `Add $${left.toFixed(2)} for free Express shipping` : 'Free Express shipping unlocked';
      const track = $('[role=progressbar]', bar); track.setAttribute('aria-valuenow', pct); $('span', track).style.width = `${pct}%`;
    });
    $('[data-cart-items]').innerHTML = cart.items.length ? cart.items.map((i) => `
      <div class="mini">
        ${i.images[0] ? `<img src="${esc(i.images[0].thumbnail)}" alt="" width="72" height="72">` : '<span class="mini__ph"></span>'}
        <div><a href="${esc(i.permalink)}">${i.name}</a><span class="mono">${esc(i.sku)}</span><span class="mini__qty">Qty ${i.quantity} · ${money(+i.totals.line_subtotal + +i.totals.line_subtotal_tax, i.totals)}</span></div>
        <button class="icon-btn" type="button" data-remove="${esc(i.key)}" aria-label="Remove ${esc(i.name)}"><svg class="icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m5 5 14 14M19 5 5 19"/></svg></button>
      </div>`).join('') : '<p class="drawer__empty">Your cart is empty.</p>';
  };
  const refresh = () => api('cart').then(paint);

  document.addEventListener('click', async (e) => {
    const add = e.target.closest('[data-add]'), rm = e.target.closest('[data-remove]');
    if (e.target.closest('[data-cart-open]')) { await refresh().catch(() => {}); openDrawer(); }
    if (e.target.closest('[data-cart-close]') || e.target.closest('[data-scrim]')) closeDrawer();
    if (add) addItem(+add.dataset.add, 1, add);
    if (rm) { if (!nonce) await refresh(); api('cart/remove-item', { method: 'POST', body: JSON.stringify({ key: rm.dataset.remove }) }).then(paint); }
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { if (!drawer.hidden) closeDrawer(); setMega(false); } });

  const addItem = async (id, quantity, btn) => {
    const label = btn.textContent; btn.disabled = true; btn.textContent = 'Adding';
    try {
      if (!nonce) await refresh();
      paint(await api('cart/add-item', { method: 'POST', body: JSON.stringify({ id, quantity }) }));
      openDrawer();
    } catch (err) { alert(err.message); }
    btn.disabled = false; btn.textContent = label;
  };
  const buy = $('[data-buy]');
  if (buy) buy.addEventListener('submit', (e) => { e.preventDefault(); addItem(+buy.dataset.buy, +$('input[name=quantity]', buy).value || 1, $('button', buy)); });

  if (document.cookie.includes('woocommerce_items_in_cart=1')) refresh().catch(() => {});

  /* Tabs */
  const tabs = $('[data-tabs]');
  if (tabs) {
    const list = $$('[role=tab]', tabs);
    const pick = (t) => { list.forEach((b) => { const on = b === t; b.setAttribute('aria-selected', on); b.tabIndex = on ? 0 : -1; $(`#${b.getAttribute('aria-controls')}`).hidden = !on; }); };
    list.forEach((b, i) => {
      b.addEventListener('click', () => pick(b));
      b.addEventListener('keydown', (e) => { const d = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0; if (d) { const n = list[(i + d + list.length) % list.length]; pick(n); n.focus(); } });
    });
    $$('[data-tab-link]').forEach((a) => a.addEventListener('click', (e) => { e.preventDefault(); const t = list.find((b) => b.getAttribute('aria-controls') === `tab-${a.dataset.tabLink}`); pick(t); t.scrollIntoView({ block: 'center' }); t.focus(); }));
  }
})();
