import { test, expect } from '@playwright/test';
import { execSync } from 'node:child_process';

const wpEval = (code) => execSync(`npx wp-env run cli wp eval ${JSON.stringify(code).replace(/\$/g, '\\$')}`, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });
const setOpts = (o) => wpEval(`$s = (array) get_option('ss_settings', []); foreach (json_decode('${JSON.stringify(o)}', true) as $k => $v) { $s[$k] = $v; } update_option('ss_settings', $s);`);
const cookie = { name: 'ss_entry', value: '1', url: 'http://localhost:8888' };

test.afterAll(() => setOpts({ partner_enabled: 0, partner_url: '', discount_enabled: 0 }));

test('11. partner note is off by default and appears only when enabled with an address', async ({ browser }) => {
  const ctx = await browser.newContext(); await ctx.addCookies([cookie]); const p = await ctx.newPage();
  await p.goto('/product/griffin-beaker-250ml/');
  await expect(p.locator('.partner-note')).toHaveCount(0);
  setOpts({ partner_enabled: 1, partner_url: '' });
  await p.reload();
  await expect(p.locator('.partner-note')).toHaveCount(0);
  setOpts({ partner_enabled: 1, partner_url: 'https://example.com/' });
  await p.reload();
  await expect(p.locator('.summary .partner-note a')).toHaveAttribute('href', 'https://example.com/');
  setOpts({ partner_enabled: 0, partner_url: '' });
  await ctx.close();
});

test('12. discount block is off by default, and applies when enabled', async ({ browser }) => {
  const ctx = await browser.newContext(); await ctx.addCookies([cookie]); const p = await ctx.newPage();
  await p.goto('/');
  await expect(p.locator('.tiers')).toHaveCount(0);
  setOpts({ discount_enabled: 1 });
  await p.reload();
  await expect(p.locator('.tiers li')).toHaveCount(3);
  await p.goto('/product/griffin-beaker-250ml/');
  await p.fill('#qty', '3');
  await p.click('.buy button');
  await p.waitForSelector('#cart-drawer:not([hidden]) .mini');
  await p.goto('/cart/');
  await expect(p.locator('.totals')).toContainText('Multi-item discount (5%)');
  setOpts({ discount_enabled: 0 });
  await ctx.close();
});

test('13. CSV import through the admin page updates by catalogue number', async ({ browser }) => {
  const p = await (await browser.newContext()).newPage();
  await p.goto('/wp-login.php');
  await p.fill('#user_login', 'admin'); await p.fill('#user_pass', 'password'); await p.click('#wp-submit');
  await p.waitForLoadState();
  await p.goto('/wp-admin/admin.php?page=sharp-science');
  await p.setInputFiles('input[name=ss_csv]', 'data/products.csv');
  await p.getByRole('button', { name: 'Import CSV' }).click();
  await expect(p.locator('.notice')).toContainText('0 created, 16 updated');
});
