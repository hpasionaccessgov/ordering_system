# Cafébara REST API

Base URL for local development: `http://localhost:8080/api`

The API speaks JSON only. Send `Accept: application/json`; for writes send
`Content-Type: application/json`. Catalog endpoints are public. Every other endpoint requires a
bearer token from `POST /api/login`.

## Authentication

```bash
curl -sS -X POST http://localhost:8080/api/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"seed_customer","password":"CafebaraTest123!"}'
```

Successful response:

```json
{
  "success": true,
  "data": {
    "token": "REPLACE_WITH_TOKEN",
    "token_type": "Bearer",
    "expires_in": 86400,
    "expires_at": 1764345600
  }
}
```

Send the token on every protected request:

```text
Authorization: Bearer REPLACE_WITH_TOKEN
```

Tokens are stored as SHA-256 hashes, expire after 24 hours and can be revoked with
`POST /api/logout`. Browser cookies do not authenticate API calls. Use `php yii seed/test-data` to
create the local `seed_customer`, `seed_staff` and `seed_admin` accounts.

## Response envelope

```json
{
  "success": true,
  "data": {},
  "meta": { "pagination": { "total": 12, "page_count": 2, "current_page": 1, "per_page": 10 } }
}
```

Errors use the same envelope with `error` instead of `data`:

```json
{
  "success": false,
  "error": {
    "code": "validation_failed",
    "message": "Please correct the errors below.",
    "errors": { "items": ["An order must contain at least one product."] }
  }
}
```

Status codes: `200` OK, `201` created, `401` missing/invalid/expired/revoked token,
`403` permission denied, `404` not found or not owned, `405` wrong method, `422` validation failed.

List endpoints accept Yii pagination query parameters `page` (zero-based) and `per-page`. The
default page size comes from the admin settings page size.

## Endpoints

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/api/login` | public | Validate credentials and issue a bearer token. |
| POST | `/api/logout` | bearer | Revoke the presented token. |
| GET | `/api/profile` | bearer | Return id, username, email, full name and status. |
| GET | `/api/products` | public | Paginated active products, name ascending. |
| GET | `/api/products/{id}` | public | One active product. |
| GET | `/api/categories` | public | Paginated active categories. |
| POST | `/api/orders` | bearer, `cart.checkout` | Place an order from explicit items. |
| GET | `/api/orders` | bearer, `order.view` | Paginated orders, newest first. |
| GET | `/api/orders/{id}` | bearer, `order.view` | One order with items. |
| POST | `/api/orders/{id}/cancel` | bearer, `order.cancel` | Cancel a cancellable order. |
| GET | `/api/cart` | bearer, `cart.index` | Current cart lines, subtotals and count. |
| POST | `/api/cart/items` | bearer, `cart.add` | Add `{product_id, quantity}` to the cart. |
| PATCH | `/api/cart/items/{id}` | bearer, `cart.update` | Set quantity; `0` removes the line. |
| DELETE | `/api/cart/items/{id}` | bearer, `cart.remove` | Remove one cart line. |
| DELETE | `/api/cart` | bearer, `cart.remove` | Empty the cart. |
| GET | `/api/loyalty` | bearer, `loyalty.view` | Balance, lifetime totals and tier. |
| GET | `/api/loyalty/transactions` | bearer, `loyalty.view` | Paginated caller-owned loyalty ledger. |
| GET | `/api/rewards` | bearer, `loyalty.view` | Active rewards mapped to active products. |
| POST | `/api/rewards/{id}/redeem` | bearer, `loyalty.redeem` | Redeem points into a free pending order. |
| GET | `/api/notifications` | bearer, `notification.index` | Paginated inbox with `q`, `read`, `category`, `sort`. |
| GET | `/api/notifications/unread-count` | bearer, `notification.index` | Unread count for the caller. |
| POST | `/api/notifications/{id}/read` | bearer, `notification.read` | Mark one visible notification read. |
| DELETE | `/api/notifications/{id}` | bearer, `notification.delete` | Delete one visible notification. |
| GET | `/api/favorites` | bearer, `favorite.index` | Paginated favorites with product details. |
| POST | `/api/favorites` | bearer, `favorite.add` | Add `{product_id}`; idempotent. |
| DELETE | `/api/favorites/{id}` | bearer, `favorite.remove` | Remove a favorite by product id. |
| GET | `/api/reviews?product_id={id}` | bearer | Product reviews and rating summary. |
| POST | `/api/reviews` | bearer, `review.create` | Review a product from one completed order. |

Customers see only their own orders. Staff and admin tokens can read all orders; cancellation
uses the same permission check as the web order queue. Order creation calls `OrderService::create`,
so prices are snapshot from the current product rows and the total is computed server-side.
`checkout_token` and other web form fields are never accepted as API credentials.

## Examples

Catalog:

```bash
curl -sS http://localhost:8080/api/products?page=0
curl -sS http://localhost:8080/api/products/33
curl -sS http://localhost:8080/api/categories
```

Order creation:

```bash
curl -sS -X POST http://localhost:8080/api/orders \
  -H 'Authorization: Bearer REPLACE_WITH_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"items":[{"product_id":33,"quantity":2}]}'
```

Order list, view and cancel:

```bash
curl -sS http://localhost:8080/api/orders -H 'Authorization: Bearer REPLACE_WITH_TOKEN'
curl -sS http://localhost:8080/api/orders/1 -H 'Authorization: Bearer REPLACE_WITH_TOKEN'
curl -sS -X POST http://localhost:8080/api/orders/1/cancel -H 'Authorization: Bearer REPLACE_WITH_TOKEN'
```

Customer self-service:

```bash
curl -sS http://localhost:8080/api/cart -H 'Authorization: Bearer REPLACE_WITH_TOKEN'
curl -sS -X POST http://localhost:8080/api/cart/items \
  -H 'Authorization: Bearer REPLACE_WITH_TOKEN' -H 'Content-Type: application/json' \
  -d '{"product_id":33,"quantity":2}'
curl -sS http://localhost:8080/api/loyalty -H 'Authorization: Bearer REPLACE_WITH_TOKEN'
curl -sS -X POST http://localhost:8080/api/rewards/1/redeem -H 'Authorization: Bearer REPLACE_WITH_TOKEN'
curl -sS http://localhost:8080/api/notifications/unread-count -H 'Authorization: Bearer REPLACE_WITH_TOKEN'
curl -sS -X POST http://localhost:8080/api/favorites \
  -H 'Authorization: Bearer REPLACE_WITH_TOKEN' -H 'Content-Type: application/json' \
  -d '{"product_id":33}'
curl -sS 'http://localhost:8080/api/reviews?product_id=33' -H 'Authorization: Bearer REPLACE_WITH_TOKEN'
```

Cart and loyalty writes use the same services as the website. Notification and favorite rows are
always scoped to the token owner; staff lose favorite permissions exactly as in the web UI. A
review needs a completed order owned by the caller that contains the reviewed product. Business
conflicts such as insufficient points or a duplicate review return `400` with the same error
envelope.

## Postman

Import `docs/cafebara-api.postman_collection.json`. The collection has a `baseUrl` variable
(`http://localhost:8080/api`) and a `token` variable that the Login request fills automatically.
Run **Auth / Login** first. **Orders / Create** stores the new order id in `orderId`; **Rewards /
Redeem** stores the reward id in `rewardId`; **Notifications / Read** and **Delete** use `notificationId`
from **Notifications / List**.

Tests: `vendor/bin/codecept run Functional ApiCest --no-colors` and
`vendor/bin/codecept run Functional ApiCustomerCest --no-colors`.
