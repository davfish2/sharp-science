# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: templates.spec.mjs >> 7. lab-use line on every template
- Location: tests/templates.spec.mjs:11:1

# Error details

```
Error: /

expect(locator).toBeVisible() failed

Locator: getByText('For laboratory and professional use').first()
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - / getByText('For laboratory and professional use').first() with timeout 5000ms
  - waiting for getByText('For laboratory and professional use').first()

```

```yaml
- link "Skip to content":
  - /url: "#main"
- region "Store notices": Research Use Only. Not for human or animal use Order now, dispatched Monday Free Express shipping over $200
- banner:
  - link "Sharp Science home":
    - /url: http://localhost:8888/
    - img "Sharp Science"
  - navigation "Main":
    - button "Shop"
    - link "Certificates":
      - /url: http://localhost:8888/certificates/
    - link "About":
      - /url: http://localhost:8888/about/
    - link "FAQ":
      - /url: http://localhost:8888/faq/
    - link "Contact":
      - /url: http://localhost:8888/contact/
  - button "Search"
  - button "Open cart"
- main:
  - heading "Precision glassware, dispatched daily." [level=1]
  - paragraph: Laboratory glassware, vials and instruments for Australian trade and professional customers.
  - link "Shop the range":
    - /url: http://localhost:8888/shop/
  - link "View certificates":
    - /url: http://localhost:8888/certificates/
  - img "Sharp Science flask, vial and beaker"
  - list:
    - listitem: COA verified by Janoshik
    - listitem: Express Post, dispatched daily
    - listitem: Free shipping over $200
    - listitem: Lab-grade supplies
  - paragraph: 01 Categories
  - heading "The range" [level=2]
  - link "Metabolic 03":
    - /url: http://localhost:8888/shop/metabolic/
  - link "Recovery 01":
    - /url: http://localhost:8888/shop/recovery/
  - link "Essentials 01":
    - /url: http://localhost:8888/shop/essentials/
  - paragraph: 02 Best sellers
  - heading "Most ordered" [level=2]
  - link "All products":
    - /url: http://localhost:8888/shop/
  - article:
    - heading "Bacteriostatic Water 10 ml" [level=3]:
      - link "Bacteriostatic Water 10 ml":
        - /url: http://localhost:8888/product/bacteriostatic-water-10ml/
    - paragraph: SS-BAC10
    - paragraph: 10 ml, 0.9% Benzyl Alcohol
    - strong: $24.00
    - button "Quick add Bacteriostatic Water 10 ml": Quick add
  - article:
    - heading "RETA-10" [level=3]:
      - link "RETA-10":
        - /url: http://localhost:8888/product/reta-10/
    - paragraph: SS-RT10
    - paragraph: 10 mg, Lyophilised
    - strong: $189.00
    - button "Quick add RETA-10": Quick add
  - article:
    - heading "SEMA-05" [level=3]:
      - link "SEMA-05":
        - /url: http://localhost:8888/product/sema-05/
    - paragraph: SS-SM05
    - paragraph: 5 mg, Lyophilised
    - strong: $129.00
    - button "Quick add SEMA-05": Quick add
  - article:
    - heading "TESA-05" [level=3]:
      - link "TESA-05":
        - /url: http://localhost:8888/product/tesa-05/
    - paragraph: SS-TS05
    - paragraph: 5 mg, Lyophilised
    - strong: $149.00
    - button "Quick add TESA-05": Quick add
  - paragraph: 03 Assurance
  - heading "Verified, not promised" [level=2]
  - list:
    - listitem:
      - text: A
      - heading "Sourced" [level=3]
      - paragraph: Selected to published glassware and instrument standards.
    - listitem:
      - text: B
      - heading "Certified" [level=3]
      - paragraph: COA verified by Janoshik.
    - listitem:
      - text: C
      - heading "Dispatched" [level=3]
      - paragraph: Express Post every dispatch day.
  - heading "Check your batch" [level=3]
  - paragraph: The batch number is printed on your label.
  - text: Batch number
  - textbox "Batch number":
    - /placeholder: B26-1015
  - button "Check"
  - paragraph: 04 FAQ
  - heading "Questions" [level=2]
  - group: When will my order ship?
  - group: How do certificates work?
  - group: Do you supply institutions?
  - group: What is your returns policy?
  - group: Who can order?
- contentinfo:
  - img "Sharp Science"
  - paragraph: Laboratory glassware, sample vials and instruments, supplied across Australia.
  - navigation "Shop":
    - heading "Shop" [level=2]
    - list:
      - listitem:
        - link "Metabolic":
          - /url: http://localhost:8888/shop/metabolic/
      - listitem:
        - link "Recovery":
          - /url: http://localhost:8888/shop/recovery/
      - listitem:
        - link "Essentials":
          - /url: http://localhost:8888/shop/essentials/
  - navigation "Information":
    - heading "Information" [level=2]
    - list:
      - listitem:
        - link "Certificates":
          - /url: http://localhost:8888/certificates/
      - listitem:
        - link "Shipping":
          - /url: http://localhost:8888/shipping/
      - listitem:
        - link "Returns":
          - /url: http://localhost:8888/returns/
      - listitem:
        - link "Terms of Sale":
          - /url: http://localhost:8888/terms-of-sale/
      - listitem:
        - link "Privacy":
          - /url: http://localhost:8888/privacy/
      - listitem:
        - link "FAQ":
          - /url: http://localhost:8888/faq/
  - heading "Contact" [level=2]
  - list:
    - listitem:
      - link "Contact us":
        - /url: http://localhost:8888/contact/
  - paragraph: Research Use Only. Not for human or animal use
  - paragraph: COA verified by Janoshik
  - paragraph: © 2026 Sharp Science · Prices in AUD including GST
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { newCtx, addFirstProductToCart } from './helpers.mjs';
  3  | 
  4  | const LAB = 'For laboratory and professional use';
  5  | 
  6  | async function expectLab(page, url) {
  7  |   await page.goto(url);
> 8  |   await expect(page.getByText(LAB).first(), url).toBeVisible();
     |                                                  ^ Error: /
  9  | }
  10 | 
  11 | test('7. lab-use line on every template', async ({ browser }) => {
  12 |   const context = await newCtx(browser);
  13 |   const page = await context.newPage();
  14 |   await page.goto('/');
  15 |   const cat = await page.locator('.tile').first().getAttribute('href');
  16 |   await page.goto('/shop/');
  17 |   const prod = await page.locator('.card a[href*="/product/"]').first().getAttribute('href');
  18 |   for (const u of ['/', '/shop/', cat, prod, '/cart/', '/certificates/', '/terms-of-sale/', '/this-page-does-not-exist-xyz/', '/?s=vial', '/?s=zzzqqqnothing']) {
  19 |     await expectLab(page, u);
  20 |   }
  21 |   await addFirstProductToCart(page);
  22 |   await expectLab(page, '/cart/');
  23 |   await expectLab(page, '/checkout/');
  24 |   await context.close();
  25 | });
  26 | 
  27 | test('8. no WordPress tells', async ({ browser }, testInfo) => {
  28 |   const context = await newCtx(browser);
  29 |   const page = await context.newPage();
  30 |   await page.goto('/shop/');
  31 |   const prod = await page.locator('.card a[href*="/product/"]').first().getAttribute('href');
  32 |   const pages = { home: '/', product: prod };
  33 |   const htmls = {};
  34 |   for (const [k, u] of Object.entries(pages)) htmls[k] = await (await context.request.get(u)).text();
  35 |   await addFirstProductToCart(page);
  36 |   htmls.checkout = await (await context.request.get('/checkout/')).text();
  37 | 
  38 |   const banned = [
  39 |     ['<meta name="generator"', /<meta\s+name=["']generator["']/i],
  40 |     ['wp-block-library', /wp-block-library/],
  41 |     ['wp-emoji', /wp-emoji/],
  42 |     ['xmlrpc.php', /xmlrpc\.php/],
  43 |     ['wlwmanifest', /wlwmanifest/],
  44 |     ['rel="EditURI"', /rel=["']EditURI["']/i],
  45 |     ["rel='shortlink'", /rel=["']shortlink["']/i],
  46 |     ['api.w.org', /api\.w\.org/],
  47 |   ];
  48 |   const found = [];
  49 |   for (const [k, html] of Object.entries(htmls)) {
  50 |     for (const [label, re] of banned) if (re.test(html)) found.push(`${k}: ${label}`);
  51 |     if (k !== 'checkout' && /<script[^>]+jquery/i.test(html)) found.push(`${k}: jquery script`);
  52 |     for (const info of ['wp-content', 'wc-block', 'woocommerce']) {
  53 |       const n = (html.match(new RegExp(info, 'gi')) || []).length;
  54 |       testInfo.annotations.push({ type: 'info', description: `${k}: ${info} x${n}` });
  55 |     }
  56 |   }
  57 |   expect(found, 'WordPress tells found').toEqual([]);
  58 |   await context.close();
  59 | });
  60 | 
```