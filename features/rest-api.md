---
slug: rest-api
title: REST API
kind: feature
domain: orders
status: active
summary: Bearer-token REST API for login, catalog browsing and customer orders.
aliases: []
entry:
  - POST /api/login
  - GET /api/products
  - POST /api/orders
files:
  - modules/api/Module.php
  - modules/api/controllers/ApiController.php
  - modules/api/controllers/AuthController.php
  - modules/api/controllers/ProductController.php
  - modules/api/controllers/CategoryController.php
  - modules/api/controllers/OrderController.php
  - modules/api/models/ApiToken.php
  - modules/api/models/OrderCreateForm.php
  - modules/api/exceptions/ApiException.php
  - models/User.php
  - migrations/m261127_140000_create_api_tokens.php
  - config/web.php
  - config/test.php
  - docs/API.md
  - docs/cafebara-api.postman_collection.json
tests:
  - tests/Functional/ApiCest.php
  - tests/Unit/Models/UserTest.php
data: [api_tokens]
config: [ApplicationSettings::pagination]
connections:
  - rest-api-customer: sub-feature: customer self-service endpoints for cart, loyalty, rewards, notifications, favorites and reviews.
hubs:
  - hub-navigation: API pretty routes live in Module::urlRules; Navigation owns only browser-facing patterns.
updated: 2026-11-27
---

# REST API

## Summary

External clients authenticate with a revocable bearer token and use JSON endpoints to browse the catalog and place, list, view and cancel orders. Orders go through the same services as web checkout and are scoped to the token owner unless an admin or staff token is used.

## Example use

`curl -X POST http://localhost:8080/api/login -H 'Content-Type: application/json' -d '{"username":"customer","password":"<password>"}'`

`curl http://localhost:8080/api/products -H 'Authorization: Bearer <token>'`

`vendor/bin/codecept run Functional ApiCest --no-colors`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| POST /api/login | `AuthController::actionLogin` | public JSON | Issues a bearer token; reuses `LoginForm` validation. |
| POST /api/logout | `AuthController::actionLogout` | bearer | Revokes the presented token. |
| GET /api/profile | `AuthController::actionProfile` | bearer | Returns non-sensitive profile fields. |
| GET /api/products | `ProductController::actionIndex` | public JSON | Paginated active products. |
| GET /api/products/{id} | `ProductController::actionView` | public JSON | One active product. |
| GET /api/categories | `CategoryController::actionIndex` | public JSON | Active categories. |
| POST /api/orders | `OrderController::actionCreate` | bearer, cart.checkout | Calls `OrderService::create` with validated items. |
| GET /api/orders | `OrderController::actionIndex` | bearer, order.view | Owner-scoped unless staff/admin. |
| GET /api/orders/{id} | `OrderController::actionView` | bearer, order.view | Owner-scoped unless staff/admin. |
| POST /api/orders/{id}/cancel | `OrderController::actionCancel` | bearer, order.cancel | Calls `OrderStatusService::cancel`. |

## Chain

- entry: `modules/api/Module` maps `/api/...` patterns and disables sessions for API routes.
- logic: `ApiController` handles bearer auth, JSON errors and envelopes; `OrderCreateForm` validates input.
- data: `ApiToken` stores only hashed tokens with expiry/revocation; `OrderService` and `OrderStatusService` own order writes.
- presentation/output: `yii\rest\Serializer` renders JSON; no views.
- config: `config/web.php` and `config/test.php` register the module; `docs/API.md` and the Postman collection document it.

## Flow

1. A client posts credentials to `/api/login`; `LoginForm::validate` applies the existing password and lockout checks.
2. `AuthController::actionLogin` issues a random token through `ApiToken::issue`, returning the token and its expiry.
3. `HttpBearerAuth` calls `User::findIdentityByAccessToken`, which resolves the token hash through `ApiToken::findActive`.
4. `AccessControl` checks the endpoint's RBAC permission against the token identity.
5. `ProductController` and `CategoryController` read active catalog rows from `Product` and `Category`.
6. `OrderCreateForm::validateItems` rejects malformed items before any write.
7. `OrderController::actionCreate` delegates to `OrderService::create`, which snapshots prices and computes the total.
8. `OrderController::actionIndex` and `actionView` scope queries with `OrderAccessPolicy::scopeToOwner`.
9. `OrderController::actionCancel` delegates to `OrderStatusService::cancel` with the token user's permission flag.
10. `ApiController::runAction` converts any exception into the same JSON error envelope with an appropriate HTTP status.

## Gotchas

- Only the SHA-256 hash of a token is stored; the raw token is shown once on login and cannot be recovered.
- API requests use a stateless user component (`enableSession = false`, `loginUrl = null`) that is restored after the request; browser cookies do not authenticate API calls.
- Guests can read the catalog, but every order endpoint requires a bearer token even if the browser has a session.
- Staff/admin tokens can read all orders; customers are scoped to `user_id` by `OrderAccessPolicy`.
- `ApiToken::findActive` rejects expired and revoked tokens; logout revokes only the presented token.
- `ApplicationSettings::pagination()` supplies the same page size for API and web lists.
- Validation failures return 422 with field errors; authentication failures return 401 and permission failures 403.
- The checkout token from `ShoppingCart` is not an API credential and is never accepted as a bearer token.

## Tests

`tests/Functional/ApiCest.php` exercises login, logout, profile, catalog, order create/list/view/cancel, ownership and error envelopes. It runs against `yii2basic_test` and creates its own users, products and orders.

## Changelog

- 2026-11-27 (feat, local): customer JSON endpoints were missing -> no self-service API -> added child routes, docs and Postman folders. Test: ApiCustomerCest
- 2026-11-27 (feat, local): REST endpoints were missing -> no JSON interface for external clients -> added bearer-auth module, docs and Postman collection. Test: ApiCest
