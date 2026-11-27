---
slug: loyalty-progress
title: Loyalty progress panel
kind: feature
domain: loyalty
status: active
summary: Loyalty page shows tier progress from lifetime earned points in both color modes.
aliases: []
entry:
  - screen /rewards
files:
  - views/loyalty/index.php
tests:
  - tests/Unit/Frontend/ThemeContrastTest.php
data: [loyalty_point_transaction]
config: [Navigation::REWARDS]
connections: []
hubs:
  - hub-theme: Progress panel uses bg-body-tertiary so its caption follows the active color mode.
updated: 2026-11-27
---

# Loyalty progress panel

## Summary

The rewards page turns the signed-in customer's lifetime earned points into a tier, next threshold and progress bar. The caption is composed at runtime and must stay readable in light and dark modes.

## Example use

http://localhost:8080/rewards

`vendor/bin/codecept run Unit --filter ThemeContrastTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| screen /rewards | `LoyaltyController::actionIndex` | screen, loyalty.index | Renders the customer's rewards overview. |

## Chain

- entry: `LoyaltyController::actionIndex` supplies the loyalty summary.
- logic: the view computes tier floor, threshold and remaining earned points.
- data: `loyalty_point_transaction` rows supply lifetime EARN totals.
- presentation: `views/loyalty/index.php` renders the panel and caption.
- config: `Navigation::REWARDS` owns the pretty URL.

## Flow

1. `LoyaltyController::actionIndex` loads the customer's balance and ledger summary.
2. The view derives tier from lifetime EARN only.
3. The view computes `remaining` from the next threshold.
4. The progress panel renders a theme-aware surface.
5. The caption states how many more earned points are needed for the next tier.

## Gotchas

- REDEEM does not downgrade a tier; ADJUST affects spendable balance only.
- The `83 more earned points to reach Silver.` caption is generated from `$remaining`, not stored in source.
- `bg-light text-dark` stays light-mode in dark mode; use `bg-body-tertiary`.

## Tests

The focused test checks the view no longer uses `bg-light text-dark` and uses the theme-aware surface instead. It does not compute tier values.

## Changelog

- 2026-11-27 (fix, local): loyalty caption was pale on a white panel in dark mode -> hardcoded bg-light and text-dark -> panel now uses bg-body-tertiary. Test: ThemeContrastTest
