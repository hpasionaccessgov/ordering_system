---
slug: cart-quantity
title: Cart quantity updates
kind: feature
domain: cart
status: active
summary: Update or clear cart line quantities from the cart page and mini cart.
aliases: []
entry:
  - POST /cart/update
  - screen /sales/cart
files:
  - controllers/CartController.php
  - services/ShoppingCart.php
  - views/cart/index.php
  - views/layouts/_mini_cart_body.php
tests:
  - tests/Unit/Services/ShoppingCartTest.php
  - tests/Unit/Services/NavigationTest.php
data: [cart_items]
config: [Navigation::CART_UPDATE_QUANTITY, Navigation::CART_INDEX]
connections: []
hubs:
  - hub-navigation: Cart forms build their POST action from Navigation::CART_UPDATE_QUANTITY.
  - hub-audit-columns: Cart rows carry created_by and updated_by from ShoppingCart::storeDatabaseItems.
updated: 2026-11-27
---

# Cart quantity updates

## Summary

Shoppers change a line quantity on the cart page or with the mini cart +/- buttons. Both paths post to the same controller action and get a redirect or JSON cart summary depending on the request type.

## Example use

http://localhost:8080/sales/cart

`vendor/bin/codecept run Unit --filter 'ShoppingCartTest|NavigationTest'`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| POST /cart/update | `CartController::actionUpdate` | route, cart.update | Quantity comes from the POST body. |
| screen /sales/cart | `CartController::actionIndex` | screen, cart.index | Renders quantity forms for owned lines. |

## Chain

- entry: `CartController::actionUpdate` reads `postedQuantity(true)`.
- logic: `ShoppingCart::update` validates quantity and rewrites stored lines.
- data: `cart_items` rows scoped by `Yii::$app->user->id`.
- presentation: `views/cart/index.php` and `views/layouts/_mini_cart_body.php` render forms.
- config: `Navigation::CART_UPDATE_QUANTITY` owns the generated URL.

## Flow

1. `CartController::actionIndex` builds a cart summary with `ShoppingCart::summary`.
2. The cart view renders one POST form per line using `Navigation::url`.
3. `CartController::actionUpdate` reads the posted quantity and product id.
4. `ShoppingCart::update` rejects invalid quantities and removes zero-quantity lines.
5. `ShoppingCart::storeDatabaseItems` replaces the user's stored cart rows.
6. `CartController::cartResponse` redirects page posts and returns JSON for AJAX posts.

## Gotchas

- The route key is `cart.update-quantity`, but the controller action is `actionUpdate`; changing one side alone makes the form 404.
- The mini cart shares the cart page action, so both AJAX and non-AJAX posts need checking.
- Quantity zero removes the line instead of storing a zero row.

## Tests

The focused command above covers quantity rules and the generated cart route. Live POST QA uses the seeded customer cart and confirms the redirect stays on `/sales/cart`.

## Changelog

- 2026-11-27 (fix, local): cart `Update` posted to a route with no action -> map pointed at `cart/update-quantity` -> route now maps to `CartController::actionUpdate`. Test: NavigationTest
