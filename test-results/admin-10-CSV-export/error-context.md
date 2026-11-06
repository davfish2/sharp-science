# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: admin.spec.mjs >> 10. CSV export
- Location: tests/admin.spec.mjs:67:1

# Error details

```
Error: expect(received).toBe(expected) // Object.is equality

Expected: "name,catalogue_no,slug,category,spec,price_aud,stock_qty,short_description,label_band_colour,featured,best_seller"
Received: "name,catalogue_no,slug,category,spec,price_aud,stock_qty,short_description,label_band_colour,featured,best_seller,private_full_name"
```

# Page snapshot

```yaml
- generic [ref=f2e2]:
  - navigation "Main menu":
    - link "Skip to main content" [ref=f2e3] [cursor=pointer]:
      - /url: "#wpbody-content"
    - link "Skip to toolbar" [ref=f2e4] [cursor=pointer]:
      - /url: "#wp-toolbar"
    - list [ref=f2e7]:
      - listitem [ref=f2e8]:
        - link "Dashboard" [ref=f2e9] [cursor=pointer]:
          - /url: index.php
          - generic [aria-hidden] [ref=f2e10]: 
        - list [ref=f2e12]:
          - listitem [ref=f2e13]:
            - link "Home" [ref=f2e14] [cursor=pointer]:
              - /url: index.php
          - listitem [ref=f2e15]:
            - link "Updates" [ref=f2e16] [cursor=pointer]:
              - /url: update-core.php
      - listitem [ref=f2e17]:
        - link "Sharp Science" [ref=f2e18] [cursor=pointer]:
          - /url: admin.php?page=sharp-science
          - generic [aria-hidden] [ref=f2e19]: 
      - listitem [aria-hidden] [ref=f2e21]
      - listitem [ref=f2e23]:
        - link "Media" [ref=f2e24] [cursor=pointer]:
          - /url: upload.php
          - generic [aria-hidden] [ref=f2e25]: 
        - list [ref=f2e27]:
          - listitem [ref=f2e28]:
            - link "Library" [ref=f2e29] [cursor=pointer]:
              - /url: upload.php
          - listitem [ref=f2e30]:
            - link "Add Media File" [ref=f2e31] [cursor=pointer]:
              - /url: media-new.php
      - listitem [ref=f2e32]:
        - link "Pages" [ref=f2e33] [cursor=pointer]:
          - /url: edit.php?post_type=page
          - generic [aria-hidden] [ref=f2e34]: 
        - list [ref=f2e36]:
          - listitem [ref=f2e37]:
            - link "All Pages" [ref=f2e38] [cursor=pointer]:
              - /url: edit.php?post_type=page
          - listitem [ref=f2e39]:
            - link "Add Page" [ref=f2e40] [cursor=pointer]:
              - /url: post-new.php?post_type=page
      - listitem [aria-hidden] [ref=f2e41]
      - listitem [ref=f2e43]:
        - link "WooCommerce" [ref=f2e44] [cursor=pointer]:
          - /url: admin.php?page=wc-admin
        - list [ref=f2e47]:
          - listitem [ref=f2e48]:
            - link "Home" [ref=f2e49] [cursor=pointer]:
              - /url: admin.php?page=wc-admin
          - listitem [ref=f2e50]:
            - link "Orders 13" [ref=f2e51] [cursor=pointer]:
              - /url: admin.php?page=wc-orders
              - text: Orders
              - generic [ref=f2e52]: "13"
          - listitem [ref=f2e53]:
            - link "Coupons" [ref=f2e54] [cursor=pointer]:
              - /url: admin.php?page=coupons-moved
          - listitem [ref=f2e55]:
            - link "Reports" [ref=f2e56] [cursor=pointer]:
              - /url: admin.php?page=wc-reports
          - listitem [ref=f2e57]:
            - link "Settings" [ref=f2e58] [cursor=pointer]:
              - /url: admin.php?page=wc-settings
          - listitem [ref=f2e59]:
            - link "Status" [ref=f2e60] [cursor=pointer]:
              - /url: admin.php?page=wc-status
          - listitem [ref=f2e61]:
            - link "Extensions" [ref=f2e62] [cursor=pointer]:
              - /url: admin.php?page=wc-admin&path=/extensions
      - listitem [ref=f2e63]:
        - link "Products" [ref=f2e64] [cursor=pointer]:
          - /url: edit.php?post_type=product
          - generic [aria-hidden] [ref=f2e65]: 
        - list [ref=f2e67]:
          - listitem [ref=f2e68]:
            - link "All Products" [ref=f2e69] [cursor=pointer]:
              - /url: edit.php?post_type=product
          - listitem [ref=f2e70]:
            - link "Add new product" [ref=f2e71] [cursor=pointer]:
              - /url: post-new.php?post_type=product
          - listitem [ref=f2e72]:
            - link "Categories" [ref=f2e73] [cursor=pointer]:
              - /url: edit-tags.php?taxonomy=product_cat&post_type=product
      - listitem [ref=f2e74]:
        - link "Certificates" [ref=f2e75] [cursor=pointer]:
          - /url: edit.php?post_type=certificate
          - generic [aria-hidden] [ref=f2e76]: 
        - list [ref=f2e78]:
          - listitem [ref=f2e79]:
            - link "Certificates" [ref=f2e80] [cursor=pointer]:
              - /url: edit.php?post_type=certificate
          - listitem [ref=f2e81]:
            - link "Add certificate" [ref=f2e82] [cursor=pointer]:
              - /url: post-new.php?post_type=certificate
      - listitem [ref=f2e83]:
        - link "Payments" [ref=f2e84] [cursor=pointer]:
          - /url: admin.php?page=wc-settings&tab=checkout&from=PAYMENTS_MENU_ITEM
      - listitem [aria-hidden] [ref=f2e87]
      - listitem [ref=f2e89]:
        - link "Appearance" [ref=f2e90] [cursor=pointer]:
          - /url: themes.php
          - generic [aria-hidden] [ref=f2e91]: 
        - list [ref=f2e93]:
          - listitem [ref=f2e94]:
            - link "Themes" [ref=f2e95] [cursor=pointer]:
              - /url: themes.php
          - listitem [ref=f2e96]:
            - link "Design" [ref=f2e97] [cursor=pointer]:
              - /url: site-editor.php
          - listitem [ref=f2e98]:
            - link "Customize" [ref=f2e99] [cursor=pointer]:
              - /url: customize.php?return=%2Fwp-admin%2Fadmin.php%3Fpage%3Dsharp-science
          - listitem [ref=f2e100]:
            - link "Fonts" [ref=f2e101] [cursor=pointer]:
              - /url: font-library.php
          - listitem [ref=f2e102]:
            - link "Theme File Editor" [ref=f2e103] [cursor=pointer]:
              - /url: theme-editor.php
      - listitem [ref=f2e104]:
        - link "Plugins" [ref=f2e105] [cursor=pointer]:
          - /url: plugins.php
          - generic [aria-hidden] [ref=f2e106]: 
        - list [ref=f2e108]:
          - listitem [ref=f2e109]:
            - link "Installed Plugins" [ref=f2e110] [cursor=pointer]:
              - /url: plugins.php
          - listitem [ref=f2e111]:
            - link "Add Plugin" [ref=f2e112] [cursor=pointer]:
              - /url: plugin-install.php
          - listitem [ref=f2e113]:
            - link "Plugin File Editor" [ref=f2e114] [cursor=pointer]:
              - /url: plugin-editor.php
      - listitem [ref=f2e115]:
        - link "Users" [ref=f2e116] [cursor=pointer]:
          - /url: users.php
          - generic [aria-hidden] [ref=f2e117]: 
        - list [ref=f2e119]:
          - listitem [ref=f2e120]:
            - link "All Users" [ref=f2e121] [cursor=pointer]:
              - /url: users.php
          - listitem [ref=f2e122]:
            - link "Add User" [ref=f2e123] [cursor=pointer]:
              - /url: user-new.php
          - listitem [ref=f2e124]:
            - link "Profile" [ref=f2e125] [cursor=pointer]:
              - /url: profile.php
      - listitem [ref=f2e126]:
        - link "Tools" [ref=f2e127] [cursor=pointer]:
          - /url: tools.php
          - generic [aria-hidden] [ref=f2e128]: 
        - list [ref=f2e130]:
          - listitem [ref=f2e131]:
            - link "Available Tools" [ref=f2e132] [cursor=pointer]:
              - /url: tools.php
          - listitem [ref=f2e133]:
            - link "Import" [ref=f2e134] [cursor=pointer]:
              - /url: import.php
          - listitem [ref=f2e135]:
            - link "Export" [ref=f2e136] [cursor=pointer]:
              - /url: export.php
          - listitem [ref=f2e137]:
            - link "Site Health" [ref=f2e138] [cursor=pointer]:
              - /url: site-health.php
          - listitem [ref=f2e139]:
            - link "Export Personal Data" [ref=f2e140] [cursor=pointer]:
              - /url: export-personal-data.php
          - listitem [ref=f2e141]:
            - link "Erase Personal Data" [ref=f2e142] [cursor=pointer]:
              - /url: erase-personal-data.php
          - listitem [ref=f2e143]:
            - link "Scheduled Actions" [ref=f2e144] [cursor=pointer]:
              - /url: tools.php?page=action-scheduler
      - listitem [ref=f2e145]:
        - link "Settings" [ref=f2e146] [cursor=pointer]:
          - /url: options-general.php
          - generic [aria-hidden] [ref=f2e147]: 
        - list [ref=f2e149]:
          - listitem [ref=f2e150]:
            - link "General" [ref=f2e151] [cursor=pointer]:
              - /url: options-general.php
          - listitem [ref=f2e152]:
            - link "Connectors" [ref=f2e153] [cursor=pointer]:
              - /url: options-connectors.php
          - listitem [ref=f2e154]:
            - link "Writing" [ref=f2e155] [cursor=pointer]:
              - /url: options-writing.php
          - listitem [ref=f2e156]:
            - link "Reading" [ref=f2e157] [cursor=pointer]:
              - /url: options-reading.php
          - listitem [ref=f2e158]:
            - link "Discussion" [ref=f2e159] [cursor=pointer]:
              - /url: options-discussion.php
          - listitem [ref=f2e160]:
            - link "Media" [ref=f2e161] [cursor=pointer]:
              - /url: options-media.php
          - listitem [ref=f2e162]:
            - link "Permalinks" [ref=f2e163] [cursor=pointer]:
              - /url: options-permalink.php
          - listitem [ref=f2e164]:
            - link "Privacy" [ref=f2e165] [cursor=pointer]:
              - /url: options-privacy.php
      - listitem [ref=f2e166]:
        - button "Collapse Main menu" [expanded] [ref=f2e167] [cursor=pointer]:
          - generic [ref=f2e169]: Collapse Menu
  - generic [ref=f2e170]:
    - generic [ref=f2e171]:
      - navigation "Toolbar":
        - menu:
          - group [ref=f2e172]:
            - menuitem "Sharp Science" [ref=f2e173] [cursor=pointer]
          - group [ref=f2e174]:
            - menuitem "Live" [ref=f2e175] [cursor=pointer]
          - group [ref=f2e176]:
            - menuitem "Ctrl+K Open command palette" [ref=f2e177] [cursor=pointer]:
              - generic [ref=f2e179]:
                - text: Ctrl+K
                - generic [ref=f2e180]: Open command palette
        - menu [ref=f2e181]:
          - group [ref=f2e182]:
            - menuitem "Howdy, admin" [ref=f2e183] [cursor=pointer]
    - main [ref=f2e184]:
      - generic [ref=f2e186]:
        - heading "Sharp Science" [level=1] [ref=f2e187]
        - generic [ref=f2e188]:
          - generic [ref=f2e189]:
            - heading "Export products" [level=3] [ref=f2e190]
            - paragraph [ref=f2e191]: Download every product as a CSV file.
            - button "Export CSV" [active] [ref=f2e193] [cursor=pointer]
          - generic [ref=f2e194]:
            - heading "Import products" [level=3] [ref=f2e195]
            - paragraph [ref=f2e196]: Rows update by catalogue number. New numbers create products.
            - generic [ref=f2e197]:
              - button "Choose File" [ref=f2e198] [cursor=pointer]
              - paragraph [ref=f2e199]:
                - button "Import CSV" [ref=f2e200] [cursor=pointer]
          - generic [ref=f2e201]:
            - heading "Product images" [level=3] [ref=f2e202]
            - paragraph [ref=f2e203]: All product images are up to date.
        - generic [ref=f2e204]:
          - heading "Wording" [level=2] [ref=f2e205]
          - text: Lab-use line
          - textbox "Lab-use line" [ref=f2e206]: Research Use Only. Not for human or animal use
          - paragraph [ref=f2e207]: Shown in the top bar, product pages, checkout and footer.
          - text: Entry notice text
          - textbox "Entry notice text" [ref=f2e208]: Sharp Science supplies trade and professional laboratory customers.
          - text: Entry notice confirmation
          - textbox "Entry notice confirmation" [ref=f2e209]: I am a trade or professional customer.
          - text: Leave button address
          - textbox "Leave button address" [ref=f2e210]: https://www.google.com.au/
          - paragraph [ref=f2e211]: Where the Leave button sends visitors.
          - text: Checkout checkbox text
          - textbox "Checkout checkbox text" [ref=f2e212]: I confirm I am a trade or professional customer and accept the Terms of Sale
          - text: Certificate lab name
          - textbox "Certificate lab name" [ref=f2e213]: Janoshik
          - paragraph [ref=f2e214]: Optional. Added to the trust line when filled in.
          - heading "Partner shop" [level=2] [ref=f2e215]
          - text: Show partner note
          - checkbox "Show partner note" [ref=f2e216] [cursor=pointer]
          - paragraph [ref=f2e217]: Needs an address below to appear.
          - text: Partner shop address
          - textbox "Partner shop address" [ref=f2e218]
          - text: Partner note text
          - textbox "Partner note text" [ref=f2e219]: Need research supplies? Visit Research Supply Shop
          - heading "Dispatch" [level=2] [ref=f2e220]
          - text: Cut-off time
          - textbox "Cut-off time" [ref=f2e221]: 12:00
          - paragraph [ref=f2e222]: 24-hour time.
          - text: Timezone
          - textbox "Timezone" [ref=f2e223]: Australia/Perth
          - text: Dispatch on weekends
          - checkbox "Dispatch on weekends" [ref=f2e224] [cursor=pointer]
          - text: Public holidays
          - textbox "Public holidays" [ref=f2e225]: 2026-12-25 2026-12-28 2027-01-01 2027-01-26
          - paragraph [ref=f2e226]: One date per line, as YYYY-MM-DD. No dispatch on these days.
          - heading "Shipping" [level=2] [ref=f2e227]
          - text: Free shipping threshold (AUD)
          - spinbutton "Free shipping threshold (AUD)" [ref=f2e228]: "200"
          - paragraph [ref=f2e229]: Includes GST.
          - heading "Discount" [level=2] [ref=f2e230]
          - text: Show multi-item discount
          - checkbox "Show multi-item discount" [ref=f2e231] [cursor=pointer]
          - paragraph [ref=f2e232]: Off by default.
          - text: Discount tiers
          - textbox "Discount tiers" [ref=f2e233]: 3:5, 6:10, 10:15
          - paragraph [ref=f2e234]: Items:percent, separated by commas.
          - heading "Business" [level=2] [ref=f2e235]
          - text: ABN
          - textbox "ABN" [ref=f2e236]
          - paragraph [ref=f2e237]: Shown in the footer when filled in.
          - text: Contact email
          - textbox "Contact email" [ref=f2e238]
          - text: Contact phone
          - textbox "Contact phone" [ref=f2e239]
          - paragraph [ref=f2e240]:
            - button "Save settings" [ref=f2e241] [cursor=pointer]
  - contentinfo [ref=f2e242]:
    - paragraph [ref=f2e243]: Sharp Science
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { newCtx, wp } from './helpers.mjs';
  3  | import fs from 'node:fs';
  4  | 
  5  | let productId = null;
  6  | 
  7  | async function login(page) {
  8  |   await page.goto('/wp-login.php');
  9  |   await page.locator('#user_login').fill('admin');
  10 |   await page.locator('#user_pass').fill('password');
  11 |   await page.locator('#wp-submit').click();
  12 |   await page.waitForURL(/wp-admin/);
  13 | }
  14 | 
  15 | test.afterAll(() => {
  16 |   if (productId) wp('post', 'delete', String(productId), '--force');
  17 |   try {
  18 |     const ids = wp('post', 'list', '--post_type=product', '--post_status=any', '--s=Test Reagent Bottle', '--field=ID')
  19 |       .split('\n').filter((l) => /^\d+$/.test(l.trim()));
  20 |     for (const i of ids) wp('post', 'delete', i.trim(), '--force');
  21 |   } catch {}
  22 | });
  23 | 
  24 | test('9. product editing as admin', async ({ browser }) => {
  25 |   test.setTimeout(180000);
  26 |   const visitor = await newCtx(browser);
  27 |   const vp = await visitor.newPage();
  28 |   const count = async () => {
  29 |     await vp.goto('/');
  30 |     const tile = vp.locator('.tile', { hasText: 'Lab Essentials' });
  31 |     return parseInt((await tile.locator('[data-tile-count]').innerText()).trim(), 10);
  32 |   };
  33 |   const before = await count();
  34 | 
  35 |   const context = await newCtx(browser, { entry: false });
  36 |   const page = await context.newPage();
  37 |   await login(page);
  38 |   await page.goto('/wp-admin/post-new.php?post_type=product');
  39 |   await page.locator('#title').fill('Test Reagent Bottle 500 ml');
  40 |   await page.locator('#_regular_price').fill('12.34');
  41 |   await page.locator('#product_catchecklist label', { hasText: 'Lab Essentials' }).locator('input').check();
  42 |   await page.locator('#ss_catalogue_no').fill('TEST-EDIT-1');
  43 |   await page.locator('#ss_spec').fill('500 ml, Clear');
  44 |   await page.locator('input[name="ss_best_seller"][type="checkbox"]').check();
  45 |   await page.locator('#publish').click();
  46 |   await page.waitForFunction(() => document.documentElement.dataset.ssRendered, null, { timeout: 90000 });
  47 |   productId = new URL(page.url()).searchParams.get('post') ||
  48 |     (await page.locator('#post_ID').inputValue());
  49 |   expect(productId).toBeTruthy();
  50 |   await context.close();
  51 | 
  52 |   await vp.goto('/shop/lab-essentials/');
  53 |   await expect(vp.getByText('Test Reagent Bottle 500 ml').first()).toBeVisible();
  54 |   expect(await count()).toBe(before + 1);
  55 | 
  56 |   await vp.goto('/shop/lab-essentials/');
  57 |   await vp.getByText('Test Reagent Bottle 500 ml').first().click();
  58 |   await vp.waitForLoadState();
  59 |   const srcs = await vp.locator('img').evaluateAll((els) => els.map((e) => e.currentSrc || e.src));
  60 |   expect(srcs.some((s) => /\.webp(\?.*)?$/.test(s)), 'webp image: ' + srcs.join(', ')).toBeTruthy();
  61 |   expect(srcs.some((s) => /label/i.test(s)), 'label image: ' + srcs.join(', ')).toBeTruthy();
  62 |   const pdf = await visitor.request.get(`/wp-content/uploads/sharp-science/${productId}/label.pdf`);
  63 |   expect(pdf.status()).toBe(200);
  64 |   await visitor.close();
  65 | });
  66 | 
  67 | test('10. CSV export', async ({ browser }) => {
  68 |   const context = await newCtx(browser, { entry: false });
  69 |   const page = await context.newPage();
  70 |   await login(page);
  71 |   await page.goto('/wp-admin/admin.php?page=sharp-science');
  72 |   const [dl] = await Promise.all([
  73 |     page.waitForEvent('download'),
  74 |     page.getByText('Export CSV').first().click(),
  75 |   ]);
  76 |   const text = fs.readFileSync(await dl.path(), 'utf8').replace(/^﻿/, '');
  77 |   const lines = text.split(/\r?\n/).filter(Boolean);
> 78 |   expect(lines[0]).toBe('name,catalogue_no,slug,category,spec,price_aud,stock_qty,short_description,label_band_colour,featured,best_seller');
     |                    ^ Error: expect(received).toBe(expected) // Object.is equality
  79 |   expect(lines.length).toBeGreaterThanOrEqual(17);
  80 |   await context.close();
  81 | });
  82 | 
```