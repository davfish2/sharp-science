// Runs the same admin conversion a client gets: opens the Sharp Science page and waits for WebP uploads.
import { chromium } from 'playwright';
const base = process.env.BASE || 'http://localhost:8888';
const b = await chromium.launch(), p = await b.newPage();
await p.goto(`${base}/wp-login.php`);
await p.fill('#user_login', 'admin'); await p.fill('#user_pass', 'password'); await p.click('#wp-submit');
await p.waitForLoadState('domcontentloaded');
await p.goto(`${base}/wp-admin/admin.php?page=sharp-science`);
await p.waitForFunction(() => document.documentElement.dataset.ssRendered !== undefined || /up to date/.test(document.getElementById('ss-status')?.textContent || ''), null, { timeout: 180000 });
console.log(await p.textContent('#ss-status'));
await b.close();
