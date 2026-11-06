# Plan

## Setup and Phase 0: reference measurement
- Tooling: Node 22, Playwright, sharp, wp-env (Docker via OrbStack), Scrapling in `.venv` (Python 3.12).
- One Sonnet agent per reference site runs `tools/capture-site.mjs`. Output is `/research/<site>.json` and screenshots.
- Public pages only, no accounts, robots.txt respected, no bot-protection bypass. A blocked site is recorded as blocked.
- Lead writes `/research/SUMMARY.md` from the JSON.

## Phase 1: design system
Tokens, WCAG ratio check, self-hosted fonts, `/design/brand-sheet.html`.

## Phase 2: logo and label
SVG mark, lockups, favicons, social image, label template and generator, print PDF, 2.5D renders. **Stop for review.**
