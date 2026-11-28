---
slug: rest-api-customer
title: Customer Self-Service REST API
kind: feature
domain: customer
status: active
summary: Bearer-token APIs for cart, loyalty, rewards, notifications, favorites and reviews.
aliases: []
entry:
  - GET /api/cart
  - GET /api/loyalty
  - GET /api/rewards
  - GET /api/notifications
  - GET /api/favorites
  - GET /api/reviews
files:
  - modules/api/Module.php
  - modules/api/controllers/CartController.php
  - modules/api/controllers/LoyaltyController.php
  - modules/api/controllers/NotificationController.php
  - modules/api/controllers/FavoriteController.php
  - modules/api/controllers/ReviewController.php
tests:
  - tests/Functional/ApiCustomerCest.php
data: [shopping-cart, loyalty_point_transactions, notifications, favorites, product_reviews]
config: [ApplicationSettings::pagination]
connections:
  - rest-api: parent: bearer auth, JSON envelope and shared /api routing.
  - reward-redemption: POST /api/rewards/{id}/redeem calls LoyaltyService::redeem.
  - notification-navigation: notification list and single-row actions reuse Notification::visibleTo.
  - cart-quantity: cart writes reuse ShoppingCart add, update, remove and clear.
hubs: []
updated: 2026-11-28
---

# Customer Self-Service REST API

## Summary

Authenticated clients manage their cart, loyalty balance, rewards, inbox, favorites and reviews through JSON endpoints. Every write delegates to the same service used by the web UI and stays scoped to the token owner.

## Example use

`curl -sS http://localhost:8080/api/cart -H 'Authorization: Bearer <token>'`

`curl -sS -X POST http://localhost:8080/api/favorites -H 'Authorization: Bearer <token>' -H 'Content-Type: application/json' -d '{"product_id":33}'`

`vendor/bin/codecept run Functional ApiCustomerCest --no-colors`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| GET /api/cart | `CartController::actionIndex` | bearer, cart.index | Returns `ShoppingCart::summary`. |
| POST /api/cart/items | `CartController::actionAdd` | bearer, cart.add | Adds `{product_id, quantity}`. |
| PATCH /api/cart/items/{id} | `CartController::actionUpdate` | bearer, cart.update | Quantity 0 removes the line. |
| DELETE /api/cart/items/{id} | `CartController::actionRemove` | bearer, cart.remove | Removes one line. |
| DELETE /api/cart | `CartController::actionClear` | bearer, cart.remove | Empties the cart. |
| GET /api/loyalty | `LoyaltyController::actionIndex` | bearer, loyalty.view | Current balance, lifetime totals and tier. |
| GET /api/loyalty/transactions | `LoyaltyController::actionTransactions` | bearer, loyalty.view | Paginated caller-owned ledger. |
| GET /api/rewards | `LoyaltyController::actionRewards` | bearer, loyalty.view | Redeemable active rewards. |
| POST /api/rewards/{id}/redeem | `LoyaltyController::actionRedeem` | bearer, loyalty.redeem | Calls `LoyaltyService::redeem`. |
| GET /api/notifications | `NotificationController::actionIndex` | bearer, notification.index | Paginated, owner-scoped inbox. |
| GET /api/notifications/unread-count | `NotificationController::actionUnreadCount` | bearer, notification.index | Returns `{"count": int}`. |
| POST /api/notifications/{id}/read | `NotificationController::actionRead` | bearer, notification.read | Marks one visible notification read. |
| DELETE /api/notifications/{id} | `NotificationController::actionDelete` | bearer, notification.delete | Deletes one visible notification. |
| GET /api/favorites | `FavoriteController::actionIndex` | bearer, favorite.index | Paginated favorites with products. |
| POST /api/favorites | `FavoriteController::actionAdd` | bearer, favorite.add | Adds `{product_id}`. |
| DELETE /api/favorites/{id} | `FavoriteController::actionRemove` | bearer, favorite.remove | Removes one favorite by product id. |
| GET /api/reviews?product_id={id} | `ReviewController::actionIndex` | bearer | Product reviews and summary. |
| POST /api/reviews | `ReviewController::actionCreate` | bearer, review.create | Calls `ReviewService::create`. |

## Chain

- entry: `modules/api/Module` maps the customer routes; each controller extends `ApiController`.
- logic: `ShoppingCart`, `LoyaltyService`, `Notification::visibleTo`, `FavoriteService` and `ReviewService` own all writes and ownership checks.
- data: account cart rows, loyalty ledger, notifications, favorites and product reviews; no new tables.
- presentation/output: `ApiController::ok` and `paginationMeta` render JSON; no views or flashes.
- config: `ApplicationSettings::pagination()` sets list page size; `docs/API.md` and the Postman collection document calls.

## Flow

1. `ApiController::init` installs the stateless bearer user and `HttpBearerAuth` resolves the token.
2. `AccessControl` checks the endpoint's RBAC permission against the token identity.
3. Cart actions call `ShoppingCart::add`, `update`, `remove` or `clear`, then return `summary()`.
4. Loyalty reads call `LoyaltyService::summary` and scoped ledger queries; redeem calls `LoyaltyService::redeem`.
5. Notification actions scope rows with `Notification::visibleTo` before count, read or delete.
6. Favorite actions call `FavoriteService::add`/`remove` with the token user's id.
7. Review creation loads `{product_id, order_id, rating, review}` and calls `ReviewService::create`.
8. `ApiController::runAction` converts exceptions into the shared JSON error envelope.

## Gotchas

- The web checkout token is not accepted here; checkout remains `POST /api/orders`.
- Staff/admin notification tokens can view all rows but only mark their own rows read-all; single-row actions still use `visibleTo`.
- Staff lose favorite permissions; `favorite.*` failures return 403 as they do in the web UI.
- Reward redemption creates a pending free order through `LoyaltyService`; it does not bypass stock and balance checks.
- Review creation requires a completed order owned by the caller that contains the reviewed product.
- Quantity 0 in `PATCH /api/cart/items/{id}` deletes the line; invalid quantities return 422.

## Tests

`tests/Functional/ApiCustomerCest.php` creates users, products, inventory, rewards and notifications, then exercises each endpoint group, ownership and validation. Run with `vendor/bin/codecept run Functional ApiCustomerCest --no-colors`.

## Changelog

- 2026-11-28 (fix, local): shared ApiController had no CORS filter -> browser clients were blocked -> added Origin allowlist inherited by these endpoints. Test: curl OPTIONS /api/cart
- 2026-11-27 (feat, local): self-service JSON endpoints were missing -> no cart, loyalty, inbox, favorites or reviews API -> added scoped controllers, routes and tests. Test: ApiCustomerCest
