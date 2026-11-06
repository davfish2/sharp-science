import { test, expect } from '@playwright/test';
import { newCtx, evalPhp } from './helpers.mjs';

const ids = {};

test.beforeAll(() => {
  for (const [sku, price] of [['TEST-19999', '199.99'], ['TEST-20000', '200.00']]) {
    ids[sku] = evalPhp(
      `$p = new WC_Product_Simple(); $p->set_name('Temp ${sku}'); $p->set_sku('${sku}'); $p->set_regular_price('${price}'); $p->set_manage_stock(false); $p->set_status('publish'); echo '@@'.$p->save().'@@';`
    );
  }
});

test.afterAll(() => {
  evalPhp(
    "foreach (['TEST-19999','TEST-20000'] as $s) { $id = wc_get_product_id_by_sku($s); if ($id) wp_delete_post($id, true); } " +
      "$q = get_posts(['post_type'=>'product','post_status'=>'any','numberposts'=>-1,'s'=>'Temp TEST-','fields'=>'ids']); foreach ($q as $i) wp_delete_post($i, true); echo '@@done@@';"
  );
});

async function rates(browser, id) {
  const context = await newCtx(browser);
  const page = await context.newPage();
  await page.goto(`/?add-to-cart=${id}`);
  await page.goto('/checkout/');
  const res = await context.request.get('/wp-json/wc/store/v1/cart');
  expect(res.ok()).toBeTruthy();
  const cart = await res.json();
  const list = (cart.shipping_rates?.[0]?.shipping_rates ?? []).map((r) => ({ name: r.name, price: r.price, total: String(Number(r.price) + Number(r.taxes)) }));
  const body = await page.locator('body').innerText();
  await context.close();
  return { list, body };
}

test('6a. 200.00 gets free Express Post', async ({ browser }) => {
  const { list, body } = await rates(browser, ids['TEST-20000']);
  expect(list.map((r) => r.name).join('|')).toMatch(/Free Express Post/i);
  expect(list.some((r) => r.total !== '0')).toBeFalsy();
  expect(body).not.toContain('$14.95');
});

test('6b. 199.99 pays Express Post 14.95', async ({ browser }) => {
  const { list, body } = await rates(browser, ids['TEST-19999']);
  expect(list.map((r) => r.name).join('|')).toMatch(/Express Post/i);
  expect(list.some((r) => /free/i.test(r.name))).toBeFalsy();
  expect(list.some((r) => r.total === '1495')).toBeTruthy();
  expect(body).toContain('$14.95');
});
