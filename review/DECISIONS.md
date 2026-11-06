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
