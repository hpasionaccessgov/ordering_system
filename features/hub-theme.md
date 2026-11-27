---
slug: hub-theme
title: Cafébara light and dark theme
kind: hub
domain: public
status: active
summary: Shared Bootstrap color-mode contract for layouts, cards, charts, text and borders.
implicit: [public, auth, catalog, cart, orders, kitchen, inventory, loyalty, notifications, reports, admin, staff, customer]
exposed_as: [data-bs-theme, CSS custom properties]
files:
  - web/css/cafebara-theme.css
  - web/js/color-mode.js
  - views/loyalty/index.php
  - views/report/_chart.php
tests:
  - tests/Unit/Frontend/ThemeContrastTest.php
updated: 2026-11-27
---

# Cafébara light and dark theme

## Summary

The theme hub defines how every screen follows `data-bs-theme`. Layouts, cards, text, icons, borders and charts must read Bootstrap tokens or Cafébara tokens that have dark overrides. Hardcoded light backgrounds, dark text and fixed gray chart labels break the contract.

## Contract

- Surfaces use `bg-body`, `bg-body-tertiary`, `bg-body-secondary` or theme-aware cards.
- Text and icons use `var(--bs-body-color)`, `var(--bs-secondary-color)` or tokens with dark overrides.
- Borders use `var(--bs-border-color)`.
- Charts read computed color tokens from `document.documentElement` in both themes.
- `color-mode.js` broadcasts `cafebara:themechange` and repaints every Chart.js canvas without a reload.
- Any literal brown in text or icon styling needs a dark override to a light sand token.

## Example use

`http://localhost:8080/rewards`

`http://localhost:8080/reports/sales`

`vendor/bin/codecept run Unit --filter ThemeContrastTest`

## Gotchas

- Bootstrap defines `bg-light` and `text-dark` only in the root color mode, so both stay light-mode colors in dark mode.
- Chart.js defaults to `#666` tick and legend text when no color is supplied.
- `--cafebara-brown` is a light-mode token; dark mode needs `--cafebara-cream` or `#e9b88c`.
- CSS changes can stay cached; hard-refresh before visual confirmation.
- Chart.js does not repaint from an attribute change alone; the theme script calls `chart.update` for each canvas.

## Changelog

- 2026-11-27 (fix, local): valid checkboxes turned green -> Bootstrap is-valid overrode brown -> forced brown for valid boxes. Test: ProductEditorFlagsTest
- 2026-11-27 (fix, local): mobile actions aligned left and menus stayed narrow -> flex-start and relative nesting -> actions align right. Test: RoleNavigationTest
- 2026-11-27 (feat, local): reward badge had no image rule -> product photo would not fill it -> added .cafebara-reward-image with cover fit. Test: RewardRedemptionTest
- 2026-11-27 (fix, local): chart labels stayed stale after a theme toggle -> Chart.js painted once -> toggle repaints every canvas. Test: ThemeContrastTest
- 2026-11-27 (feat, local): kitchen scroll and rank chip lacked themed colors -> raw colors -> used var(--bs-*) tokens. Test: KitchenBoardTest
- 2026-11-27 (feat, local): highlights had no themed surface -> no wrapping card -> added a token-based panel. Test: ProductHighlightsTest
- 2026-11-27 (fix, local): loyalty caption and chart labels stayed light -> hardcoded colors -> switched to tokens and dark overrides. Test: ThemeContrastTest
