import { test, expect } from '@playwright/test';
import { newCtx, addFirstProductToCart } from './helpers.mjs';

const LAB = 'For laboratory and professional use';

async function expectLab(page, url) {
  await page.goto(url);
  await expect(page.getByText(LAB).first(), url).toBeVisible();
}

test('7. lab-use line on every template', async ({ browser }) => {
  const context = await newCtx(browser);
  const page = await context.newPage();
  await page.goto('/');
  const cat = await page.locator('.tile').first().getAttribute('href');
  await page.goto('/shop/');
  const prod = await page.locator('.card a[href*="/product/"]').first().getAttribute('href');
  for (const u of ['/', '/shop/', cat, prod, '/cart/', '/certificates/', '/terms-of-sale/', '/this-page-does-not-exist-xyz/', '/?s=vial', '/?s=zzzqqqnothing']) {
    await expectLab(page, u);
  }
  await addFirstProductToCart(page);
  await expectLab(page, '/cart/');
  await expectLab(page, '/checkout/');
  await context.close();
});

test('8. no WordPress tells', async ({ browser }, testInfo) => {
  const context = await newCtx(browser);
  const page = await context.newPage();
  await page.goto('/shop/');
  const prod = await page.locator('.card a[href*="/product/"]').first().getAttribute('href');
  const pages = { home: '/', product: prod };
  const htmls = {};
  for (const [k, u] of Object.entries(pages)) htmls[k] = await (await context.request.get(u)).text();
  await addFirstProductToCart(page);
  htmls.checkout = await (await context.request.get('/checkout/')).text();

  const banned = [
    ['<meta name="generator"', /<meta\s+name=["']generator["']/i],
    ['wp-block-library', /wp-block-library/],
    ['wp-emoji', /wp-emoji/],
    ['xmlrpc.php', /xmlrpc\.php/],
    ['wlwmanifest', /wlwmanifest/],
    ['rel="EditURI"', /rel=["']EditURI["']/i],
    ["rel='shortlink'", /rel=["']shortlink["']/i],
    ['api.w.org', /api\.w\.org/],
  ];
  const found = [];
  for (const [k, html] of Object.entries(htmls)) {
    for (const [label, re] of banned) if (re.test(html)) found.push(`${k}: ${label}`);
    if (k !== 'checkout' && /<script[^>]+jquery/i.test(html)) found.push(`${k}: jquery script`);
    for (const info of ['wp-content', 'wc-block', 'woocommerce']) {
      const n = (html.match(new RegExp(info, 'gi')) || []).length;
      testInfo.annotations.push({ type: 'info', description: `${k}: ${info} x${n}` });
    }
  }
  expect(found, 'WordPress tells found').toEqual([]);
  await context.close();
});
