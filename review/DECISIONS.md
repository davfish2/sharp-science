# Decisions

| # | Decision | Why |
|---|----------|-----|
| 1 | Reference capture uses ordinary Playwright and Scrapling's plain fetcher. Stealth or anti-bot modes are not used. | The brief says public pages only. A site that refuses automated access is logged as blocked. |
| 2 | Python tooling runs on 3.12 in `.venv`, because the system Python is 3.15. | Scrapling's dependencies are not reliably built for 3.15. |
| 3 | Ruflo and Context7 MCP tools named in the global instructions are not available in this session. | Nothing to call. Library docs are read from installed packages. |
| 4 | All four reference captures were blocked (see `/research/SUMMARY.md`). Work continued from the brief, using the type measurements available from primepeptides.co. | The brief fully specifies the design, and Phase 0 only refines it. Bypassing the checks was ruled out. |
| 5 | Pipetting band changed from #847A99 to #8A80A0. | Graphite on the original measured 4.42, below AA. It now measures 4.80. |
| 6 | Brass on Bone is banned for text. | It measures 2.81, below the 3.0 large-text minimum. |
| 7 | White #FFFFFF added to the palette as a surface. | The brief asks for white surfaces but the palette had no pure white. |
| 8 | `/data/products.csv` was created in Phase 2, not Phase 5. | The label and render generators read it. |
| 9 | Crimp caps left out of the demo range. | The example list has 17 items and the brief asks for 16. Crimp caps are the easiest to add later. |
| 10 | Printed label names drop a size that repeats the boxed spec, e.g. "Borosilicate Serum Vial" with a "10 ml" box. | Avoids printing the same figure twice. The full name stays on the site. |
| 11 | Label QR codes use the placeholder domain `sharpscience.example`, set by `QR_BASE`. | The client's domain is unknown. |
| 12 | Favicons use a heavier cut of the mark. | The standard cut loses the Brass line at 16px. |
| 13 | The logo, label template and renders were written by the lead, not a Sonnet agent. | They are design-critical, and Phase 3 has to port them. |
| 14 | `/tools`, `package.json` and the lock file are tracked as well as the listed folders. | The brand assets cannot be rebuilt without them. |
| 15 | Brand direction approved by the developer on review. | End of Phase 2 gate. |
| 16 | Requirement 3 changed: partner shop URL blank and the note disabled by default. The setting remains. | Developer instruction. Only lawful lab supplies are in this build. |
| 17 | Requirements 5 and 6 changed: the 21+ gate and checkbox are replaced by trade and professional customer wording. There is no age field or age setting. | Developer instruction. |
| 18 | QR domain stays on the placeholder until the client's domain is known. | Developer instruction. |
| 19 | Classic PHP theme, not a block theme. | A block theme keeps too many default styles, which conflicts with the no-WordPress-look requirement. |
| 20 | Renders are written as SVG in PHP and converted to WebP in the admin browser, on the edit screen or the Sharp Science page. | Rasterising SVG on the server needs Imagick with librsvg or Chromium, which ordinary hosting rarely has. |
| 21 | QR encoder and PDF writer are written in plain PHP inside the plugin. | No Composer dependency, and it runs on any host. |
| 22 | Block checkout keeps two WooCommerce stylesheets (`packages-style.css`, `checkout.css`), overridden by ours. | The block checkout does not lay out without them. Everything else from WooCommerce and core is removed. |
| 23 | Express Post placeholder is entered as $13.59 excluding GST, which shows as $14.95. | WooCommerce flat rates are entered excluding tax. |
| 24 | Home best sellers show full rows of four only. | Six flagged products would leave gaps in the grid. |
| 25 | Certificates are marked "Sample data", "Demo Laboratory", and the page and PDFs say they are not real test records. | A demo must not present invented test records as genuine. |
| 26 | A cash-style "Pay on invoice (demo)" method is the only payment option. | No payment gateway was specified, and it lets the checkout test finish. |
| 27 | Label QR uses the placeholder domain in the local environment and `home_url()` elsewhere. | It becomes correct at launch with no change. |
