import { test, expect } from '@playwright/test';
import { newCtx } from './helpers.mjs';

test('2. browse without login', async ({ browser }) => {
  test.setTimeout(240000);
  const context = await newCtx(browser);
  const page = await context.newPage();
  const visit = async (url) => {
    const res = await page.goto(url);
    expect(res.status(), url).toBe(200);
    expect(page.url(), url).not.toContain('wp-login');
  };
  await visit('/');
  const cats = await page.locator('.tile').evaluateAll((els) => els.map((e) => e.href));
  expect(new Set(cats).size).toBe(6);
  await visit('/shop/');
  const products = [...new Set(await page.locator('a[href*="/product/"]').evaluateAll((els) => els.map((e) => e.href)))];
  expect(products.length).toBeGreaterThanOrEqual(16);
  for (const c of cats) await visit(c);
  for (const p of products) await visit(p);
  await context.close();
});
