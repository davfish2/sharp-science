import { execFileSync } from 'node:child_process';

export function wp(...args) {
  return execFileSync('npx', ['wp-env', 'run', 'cli', 'wp', ...args], {
    cwd: new URL('..', import.meta.url).pathname,
    encoding: 'utf8',
    maxBuffer: 20 * 1024 * 1024,
  });
}

// Run PHP via wp eval; PHP must echo markers "@@" ... "@@".
export function evalPhp(code) {
  const out = wp('eval', code);
  const m = out.match(/@@([\s\S]*?)@@/);
  if (!m) throw new Error('No result from wp eval: ' + out);
  return m[1].trim();
}

export function newestOrderId() {
  return evalPhp(
    "$o = wc_get_orders(['limit'=>1,'orderby'=>'id','order'=>'DESC','return'=>'ids','status'=>array_keys(wc_get_order_statuses())]); echo '@@'.(empty($o)?0:$o[0]).'@@';"
  );
}

export async function newCtx(browser, { entry = true } = {}) {
  const context = await browser.newContext({ baseURL: 'http://localhost:8888' });
  if (entry) {
    await context.addCookies([{ name: 'ss_entry', value: '1', url: 'http://localhost:8888' }]);
  }
  return context;
}

export async function addFirstProductToCart(page) {
  await page.goto('/shop/');
  const href = await page.locator('.card a[href*="/product/"]').first().getAttribute('href');
  await page.goto(href);
  await page.locator('.buy button').click();
  await page.waitForSelector('#cart-drawer .mini');
}

export async function fillCheckout(page) {
  await page.locator('#email').fill('buyer@example.com');
  await page.locator('#shipping-first_name').fill('Test');
  await page.locator('#shipping-last_name').fill('Buyer');
  await page.locator('#shipping-address_1').fill('1 Test Street');
  await page.locator('#shipping-city').fill('Perth');
  await page.locator('#shipping-postcode').fill('6000');
}
