import { test, expect } from '@playwright/test';
import { newCtx } from './helpers.mjs';

test('1. entry notice behaviour', async ({ browser }) => {
  const context = await newCtx(browser, { entry: false });
  const page = await context.newPage();
  await page.goto('/');
  const notice = page.locator('#entry-notice');
  await expect(notice).toBeVisible();
  await expect(notice).toContainText(/trade and professional/i);
  await expect(notice.locator('a[href*="terms-of-sale"]')).toHaveCount(1);
  await expect(notice.getByRole('link', { name: /terms of sale/i })).toBeVisible();
  await notice.locator('[data-enter]').click();
  await expect(notice).toBeHidden();
  const c = (await context.cookies()).find((x) => x.name === 'ss_entry');
  expect(c).toBeTruthy();
  const days = (c.expires - Date.now() / 1000) / 86400;
  expect(days).toBeGreaterThan(28);
  expect(days).toBeLessThan(32);
  await page.reload();
  await expect(page.locator('#entry-notice')).toBeHidden();
  await context.close();
});

test('1b. plain HTML is crawlable and notice is hidden attr', async ({ request }) => {
  const res = await request.get('/');
  expect(res.status()).toBe(200);
  const html = await res.text();
  expect(html).toMatch(/<h1[\s>]/);
  expect(html).toMatch(/href="[^"]*\/product\/[^"]+"/);
  expect(html).toMatch(/<[^>]*id="entry-notice"[^>]*\shidden[\s>=]/);
});
