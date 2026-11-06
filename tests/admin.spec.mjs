import { test, expect } from '@playwright/test';
import { newCtx, wp } from './helpers.mjs';
import fs from 'node:fs';

let productId = null;

async function login(page) {
  await page.goto('/wp-login.php');
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('password');
  await page.locator('#wp-submit').click();
  await page.waitForURL(/wp-admin/);
}

test.afterAll(() => {
  if (productId) wp('post', 'delete', String(productId), '--force');
  try {
    const ids = wp('post', 'list', '--post_type=product', '--post_status=any', '--s=Test Reagent Bottle', '--field=ID')
      .split('\n').filter((l) => /^\d+$/.test(l.trim()));
    for (const i of ids) wp('post', 'delete', i.trim(), '--force');
  } catch {}
});

test('9. product editing as admin', async ({ browser }) => {
  test.setTimeout(180000);
  const visitor = await newCtx(browser);
  const vp = await visitor.newPage();
  const count = async () => {
    await vp.goto('/');
    const tile = vp.locator('.tile', { hasText: 'Lab Essentials' });
    return parseInt((await tile.locator('[data-tile-count]').innerText()).trim(), 10);
  };
  const before = await count();

  const context = await newCtx(browser, { entry: false });
  const page = await context.newPage();
  await login(page);
  await page.goto('/wp-admin/post-new.php?post_type=product');
  await page.locator('#title').fill('Test Reagent Bottle 500 ml');
  await page.locator('#_regular_price').fill('12.34');
  await page.locator('#product_catchecklist label', { hasText: 'Lab Essentials' }).locator('input').check();
  await page.locator('#ss_catalogue_no').fill('TEST-EDIT-1');
  await page.locator('#ss_spec').fill('500 ml, Clear');
  await page.locator('input[name="ss_best_seller"][type="checkbox"]').check();
  await page.locator('#publish').click();
  await page.waitForFunction(() => document.documentElement.dataset.ssRendered, null, { timeout: 90000 });
  productId = new URL(page.url()).searchParams.get('post') ||
    (await page.locator('#post_ID').inputValue());
  expect(productId).toBeTruthy();
  await context.close();

  await vp.goto('/shop/lab-essentials/');
  await expect(vp.getByText('Test Reagent Bottle 500 ml').first()).toBeVisible();
  expect(await count()).toBe(before + 1);

  await vp.goto('/shop/lab-essentials/');
  await vp.getByText('Test Reagent Bottle 500 ml').first().click();
  await vp.waitForLoadState();
  const srcs = await vp.locator('img').evaluateAll((els) => els.map((e) => e.currentSrc || e.src));
  expect(srcs.some((s) => /\.webp(\?.*)?$/.test(s)), 'webp image: ' + srcs.join(', ')).toBeTruthy();
  expect(srcs.some((s) => /label/i.test(s)), 'label image: ' + srcs.join(', ')).toBeTruthy();
  const pdf = await visitor.request.get(`/wp-content/uploads/sharp-science/${productId}/label.pdf`);
  expect(pdf.status()).toBe(200);
  await visitor.close();
});

test('10. CSV export', async ({ browser }) => {
  const context = await newCtx(browser, { entry: false });
  const page = await context.newPage();
  await login(page);
  await page.goto('/wp-admin/admin.php?page=sharp-science');
  const [dl] = await Promise.all([
    page.waitForEvent('download'),
    page.getByText('Export CSV').first().click(),
  ]);
  const text = fs.readFileSync(await dl.path(), 'utf8').replace(/^﻿/, '');
  const lines = text.split(/\r?\n/).filter(Boolean);
  expect(lines[0]).toBe('name,catalogue_no,slug,category,spec,price_aud,stock_qty,short_description,label_band_colour,featured,best_seller');
  expect(lines.length).toBeGreaterThanOrEqual(17);
  await context.close();
});
