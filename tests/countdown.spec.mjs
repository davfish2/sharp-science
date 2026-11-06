import { test, expect } from '@playwright/test';
import { newCtx } from './helpers.mjs';

const cases = [
  ['2026-11-10T03:59:00Z', 'Order in the next 0h 1m for same-day dispatch'],
  ['2026-11-10T04:01:00Z', 'Order now, dispatched tomorrow'],
  ['2026-11-13T04:01:00Z', 'Order now, dispatched Monday'],
  ['2026-12-24T04:01:00Z', 'Order now, dispatched Tuesday'],
];

for (const [iso, text] of cases) {
  test(`5. countdown ${iso}`, async ({ browser }) => {
    const context = await newCtx(browser);
    const page = await context.newPage();
    await page.clock.setFixedTime(new Date(iso));
    await page.goto('/');
    await expect(page.locator('[data-dispatch]').first()).toHaveText(text);
    await context.close();
  });
}
