---
slug: cart-add-feedback
title: Cart add feedback
kind: feature
domain: cart
status: active
summary: Adding a product shows a toast with the new count and a direct View cart action.
aliases: []
entry:
  - POST /sales/cart/add
files:
  - views/layouts/_mini_cart.php
  - views/shared/_product_card.php
  - web/js/mini-cart.js
  - web/css/cafebara-theme.css
tests:
  - tests/Unit/Frontend/CartAddFeedbackTest.php
  - tests/Functional/CartSummaryCest.php
data: []
config: []
connections:
  - cart-quantity: CartController::cartResponse supplies the same message, count and cart HTML for add and update AJAX replies.
hubs:
  - hub-navigation: the View cart button opens the shared offcanvas whose summary URL comes from Navigation::CART_SUMMARY.
  - hub-theme: the toast uses theme-aware Bootstrap surfaces in both color modes.
updated: 2026-11-27
---

# Cart add feedback

## Summary

A successful AJAX add updates the count badge and shows a short toast with a View cart action. Mobile shoppers get confirmation without opening the three-dot menu; page posts keep their flash message.

## Example use

http://localhost:8080/catalog/products

`vendor/bin/codecept run Unit --filter CartAddFeedbackTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| POST /sales/cart/add | `CartController::actionAdd` | POST, cart.add | AJAX reply drives the toast; page posts still redirect with a flash. |

## Chain

- entry: `views/shared/_product_card.php` posts the per-card add form with `data-cart-ajax` and the card quantity.
- logic: `cartResponse` returns `message`, `count` and rendered mini-cart HTML.
- data: `ShoppingCart` keeps the authoritative lines.
- presentation: `_mini_cart.php` renders the toast; `mini-cart.js` shows it.
- config: `Navigation::CART_SUMMARY` supplies the drawer refresh URL.

## Flow

1. A shopper types a quantity and clicks Add to cart on a product card.
2. `mini-cart.js` copies the card quantity into the form's hidden `quantity` and posts it as AJAX.
3. `CartController::actionAdd` adds that many units and returns the cart summary.
4. `applyCart` refreshes the mini cart and every `[data-cart-count]` badge.
5. `showToast` displays the returned message with the new item count at the top of the screen.
6. The shopper taps View cart to open the offcanvas drawer.

## Gotchas

- The toast only appears for AJAX replies with a non-empty message; drawer refreshes stay silent.
- Non-AJAX adds still redirect and use the flash widget instead of the toast.
- Roles with `cart.add` also hold `cart.index`, so the drawer and toast are both rendered.
- The card quantity input belongs to the batch form; the per-card add copies its value on submit and falls back to one.
- The toast is top-right on desktop and full-width at the top on mobile.

## Tests

The unit test checks the toast markup, quantity wiring and top placement. The functional cart test proves an AJAX add applies its posted quantity. Browser animation is not driven.

## Changelog

- 2026-11-27 (fix, local): card add ignored quantity and toast sat bottom -> hidden stayed 1 -> JS syncs qty and toast moves to top. Test: CartAddFeedbackTest
- 2026-11-27 (feat, local): adding on mobile only updated a hidden header badge -> no visible confirmation -> AJAX add now shows a toast with count and a View cart action. Test: CartAddFeedbackTest
