/* Live dispatch countdown and the entry notice. No dependencies. */
(() => {
  const C = window.SS_CORE;
  if (!C) return;

  const partsIn = (date) => {
    const f = new Intl.DateTimeFormat('en-AU', { timeZone: C.timezone, hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', weekday: 'long' });
    const o = {};
    f.formatToParts(date).forEach((p) => { o[p.type] = p.value; });
    return { ymd: `${o.year}-${o.month}-${o.day}`, weekday: o.weekday, secs: +o.hour * 3600 + +o.minute * 60 + +o.second };
  };
  const shift = (ymd, n) => { const d = new Date(`${ymd}T00:00:00Z`); d.setUTCDate(d.getUTCDate() + n); return d; };
  const isDay = (d) => {
    const dow = d.getUTCDay(), ymd = d.toISOString().slice(0, 10);
    return (C.weekends || (dow !== 0 && dow !== 6)) && !C.holidays.includes(ymd);
  };

  const message = () => {
    const now = partsIn(new Date());
    const [h, m] = C.cutoff.split(':').map(Number), cut = h * 3600 + m * 60;
    if (isDay(shift(now.ymd, 0)) && now.secs < cut) {
      const mins = Math.ceil((cut - now.secs) / 60);
      return `Order in the next ${Math.floor(mins / 60)}h ${mins % 60}m for same-day dispatch`;
    }
    let n = 1;
    while (n < 15 && !isDay(shift(now.ymd, n))) n++;
    const day = shift(now.ymd, n).toLocaleDateString('en-AU', { weekday: 'long', timeZone: 'UTC' });
    return `Order now, dispatched ${n === 1 ? 'tomorrow' : day}`;
  };
  const tick = () => { const t = message(); document.querySelectorAll('[data-dispatch]').forEach((el) => { if (el.textContent !== t) el.textContent = t; }); };
  tick();
  setInterval(tick, 15000);

  // WooCommerce's checkout puts aria-label on role-less divs. A group role makes that valid.
  const summaryRole = () => document.querySelectorAll('.wc-block-checkout :is(div,span)[aria-label]:not([role])').forEach((el) => el.setAttribute('role', 'group'));
  if (document.body.classList.contains('is-checkout')) { summaryRole(); new MutationObserver(summaryRole).observe(document.body, { childList: true, subtree: true }); }

  const gate = document.getElementById('entry-notice');
  if (!gate) return;
  if (document.cookie.split('; ').some((c) => c.startsWith('ss_entry=1'))) return;
  const prior = document.activeElement;
  gate.hidden = false;
  document.documentElement.classList.add('has-notice');
  gate.querySelector('[data-enter]').focus();
  gate.addEventListener('keydown', (e) => {
    if (e.key !== 'Tab') return;
    const f = [...gate.querySelectorAll('a,button')], a = f[0], z = f[f.length - 1];
    if (e.shiftKey && document.activeElement === a) { e.preventDefault(); z.focus(); }
    if (!e.shiftKey && document.activeElement === z) { e.preventDefault(); a.focus(); }
  });
  gate.querySelector('[data-enter]').addEventListener('click', () => {
    const secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `ss_entry=1; Max-Age=${60 * 60 * 24 * 30}; Path=/; SameSite=Lax${secure}`;
    gate.hidden = true;
    document.documentElement.classList.remove('has-notice');
    if (prior && prior.focus) prior.focus();
  });
})();
