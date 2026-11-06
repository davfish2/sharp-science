import { test, expect } from '@playwright/test';
import { newCtx, addFirstProductToCart, fillCheckout, newestOrderId, evalPhp } from './helpers.mjs';

const PLACE = '.wc-block-components-checkout-place-order-button';

test('3. guest checkout completes and records confirmation', async ({ browser }) => {
  const context = await newCtx(browser);
  const page = await context.newPage();
  await addFirstProductToCart(page);
  await page.goto('/checkout/');
  await fillCheckout(page);
  await page.getByLabel(/trade or professional customer/).check();
  await page.locator(PLACE).click();
  await page.waitForURL(/order-received/, { timeout: 45000 });
  const id = newestOrderId();
  const v = evalPhp("echo '@@'.wc_get_order(" + Number(id) + ")->get_meta('_ss_confirmed_at').'@@';");
  expect(v).not.toBe('');
  await context.close();
});

test('4. checkout blocked until ticked', async ({ browser }) => {
  const context = await newCtx(browser);
  const page = await context.newPage();
  await addFirstProductToCart(page);
  await page.goto('/checkout/');
  await fillCheckout(page);
  const before = newestOrderId();
  await page.locator(PLACE).click();
  await page.waitForTimeout(3000);
  expect(page.url()).toContain('/checkout');
  expect(page.url()).not.toContain('order-received');
  expect(newestOrderId()).toBe(before);
  await page.getByLabel(/trade or professional customer/).check();
  await page.locator(PLACE).click();
  await page.waitForURL(/order-received/, { timeout: 45000 });
  expect(newestOrderId()).not.toBe(before);
  await context.close();
});
