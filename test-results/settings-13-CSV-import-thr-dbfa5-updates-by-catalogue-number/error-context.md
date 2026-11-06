# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: settings.spec.mjs >> 13. CSV import through the admin page updates by catalogue number
- Location: tests/settings.spec.mjs:41:1

# Error details

```
Error: expect(locator).toContainText(expected) failed

Locator: locator('.notice')
Timeout: 5000ms
- Expected substring  - 1
+ Received string     + 2

- 0 created, 16 updated
+ Import finished: 0 created, 5 updated.
+ 			

Call log:
  - Expect "toContainText" locator('.notice') with timeout 5000ms
  - waiting for locator('.notice')
    14 × locator resolved to <div class="notice notice-success">…</div>
       - unexpected value "Import finished: 0 created, 5 updated.
			"

```

```yaml
- paragraph: "Import finished: 0 created, 5 updated."
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { execSync } from 'node:child_process';
  3  | 
  4  | const wpEval = (code) => execSync(`npx wp-env run cli wp eval ${JSON.stringify(code).replace(/\$/g, '\\$')}`, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });
  5  | const setOpts = (o) => wpEval(`$s = (array) get_option('ss_settings', []); foreach (json_decode('${JSON.stringify(o)}', true) as $k => $v) { $s[$k] = $v; } update_option('ss_settings', $s);`);
  6  | const cookie = { name: 'ss_entry', value: '1', url: 'http://localhost:8888' };
  7  | 
  8  | test.afterAll(() => setOpts({ partner_enabled: 0, partner_url: '', discount_enabled: 0 }));
  9  | 
  10 | test('11. partner note is off by default and appears only when enabled with an address', async ({ browser }) => {
  11 |   const ctx = await browser.newContext(); await ctx.addCookies([cookie]); const p = await ctx.newPage();
  12 |   await p.goto('/product/griffin-beaker-250ml/');
  13 |   await expect(p.locator('.partner-note')).toHaveCount(0);
  14 |   setOpts({ partner_enabled: 1, partner_url: '' });
  15 |   await p.reload();
  16 |   await expect(p.locator('.partner-note')).toHaveCount(0);
  17 |   setOpts({ partner_enabled: 1, partner_url: 'https://example.com/' });
  18 |   await p.reload();
  19 |   await expect(p.locator('.summary .partner-note a')).toHaveAttribute('href', 'https://example.com/');
  20 |   setOpts({ partner_enabled: 0, partner_url: '' });
  21 |   await ctx.close();
  22 | });
  23 | 
  24 | test('12. discount block is off by default, and applies when enabled', async ({ browser }) => {
  25 |   const ctx = await browser.newContext(); await ctx.addCookies([cookie]); const p = await ctx.newPage();
  26 |   await p.goto('/');
  27 |   await expect(p.locator('.tiers')).toHaveCount(0);
  28 |   setOpts({ discount_enabled: 1 });
  29 |   await p.reload();
  30 |   await expect(p.locator('.tiers li')).toHaveCount(3);
  31 |   await p.goto('/product/griffin-beaker-250ml/');
  32 |   await p.fill('#qty', '3');
  33 |   await p.click('.buy button');
  34 |   await p.waitForSelector('#cart-drawer:not([hidden]) .mini');
  35 |   await p.goto('/cart/');
  36 |   await expect(p.locator('.totals')).toContainText('Multi-item discount (5%)');
  37 |   setOpts({ discount_enabled: 0 });
  38 |   await ctx.close();
  39 | });
  40 | 
  41 | test('13. CSV import through the admin page updates by catalogue number', async ({ browser }) => {
  42 |   const p = await (await browser.newContext()).newPage();
  43 |   await p.goto('/wp-login.php');
  44 |   await p.fill('#user_login', 'admin'); await p.fill('#user_pass', 'password'); await p.click('#wp-submit');
  45 |   await p.waitForLoadState();
  46 |   await p.goto('/wp-admin/admin.php?page=sharp-science');
  47 |   await p.setInputFiles('input[name=ss_csv]', 'data/products.csv');
  48 |   await p.getByRole('button', { name: 'Import CSV' }).click();
> 49 |   await expect(p.locator('.notice')).toContainText('0 created, 16 updated');
     |                                      ^ Error: expect(locator).toContainText(expected) failed
  50 | });
  51 | 
```