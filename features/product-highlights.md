---
slug: product-highlights
title: Product highlights
kind: feature
domain: catalog
status: active
summary: Shared specials and featured-drinks sections for home and the three role dashboards.
aliases: []
entry:
  - GET /
  - GET /customer/dashboard
  - GET /staff/dashboard
  - GET /admin/dashboard
files:
  - controllers/SiteController.php
  - controllers/CustomerController.php
  - controllers/StaffController.php
  - controllers/AdminController.php
  - views/shared/_product_highlights.php
  - views/site/index.php
  - views/customer/index.php
  - views/staff/index.php
  - views/admin/index.php
tests:
  - tests/Unit/Frontend/ProductHighlightsTest.php
  - tests/Unit/widgets/CustomerDashboardViewTest.php
  - tests/Functional/BatchCartCest.php
data: [products.is_special, products.is_featured]
config: [Navigation::MENU]
connections: []
hubs:
  - hub-theme: Highlight headings, cards and batch form use color-mode tokens.
  - hub-navigation: Browse links use Navigation::url.
updated: 2026-11-27
---

# Product highlights

## Summary

One shared partial renders Today’s specials and Featured drinks & bites from active products. Home and the three role dashboards use it; the public menu and product catalog stay as plain all-product listings.

## Example use

http://localhost:8080/

http://localhost:8080/customer/dashboard

`vendor/bin/codecept run Unit --filter 'ProductHighlightsTest|CustomerDashboardViewTest'`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| GET / | `SiteController::actionIndex` | screen, guest | Home highlights. |
| GET /customer/dashboard | `CustomerController::actionIndex` | screen, customer.access | Customer ordering shortcuts. |
| GET /staff/dashboard | `StaffController::actionIndex` | screen, staff.access | Staff view of promoted items. |
| GET /admin/dashboard | `AdminController::actionIndex` | screen, admin.access | Admin view of promoted items. |

## Chain

- entry: controllers load active special and featured products with relations.
- logic: the shared partial computes special IDs and assigns one batch form input per product.
- data: `products.is_special` and `products.is_featured` flag the two lists.
- presentation: `views/shared/_product_highlights.php` renders cards with `views/shared/_product_card.php`.
- config: `Navigation::MENU` supplies the browse link.

## Flow

1. A controller loads up to eight active special products and eight active featured products.
2. The view passes both lists to `views/shared/_product_highlights.php`.
3. The partial hides itself when the caller cannot view products or both lists are empty.
4. The partial renders one shared batch form when `cart.add` is allowed.
5. The partial renders Today’s specials cards first.
6. Featured cards reuse any special card’s batch input rather than adding a duplicate.
7. A browse link sends the visitor to the public menu.

## Gotchas

- A product can be both special and featured; its featured card must use `batchFormId => null` to avoid a duplicate input.
- Guests can view products but cannot use batch add; keep the batch form permission-gated.
- Dashboard permission checks still decide whether the highlights render at all.
- `/menu` and `/catalog/products` deliberately list all products; do not add the highlights partial there.
- Home must pass `featuredProducts`; passing only `featuredDataProvider` renders the featured heading without cards.

## Tests

The focused command checks the shared partial, the four showcasing views and the absence of the partial on menu/catalog. The customer dashboard test covers the permission guard; the functional batch-cart test covers a product that is both special and featured.

## Changelog

- 2026-11-27 (fix, local): home showed the featured heading with no cards -> actionIndex passed only the data provider -> passed its models as featuredProducts. Test: ProductHighlightsTest
- 2026-11-27 (refactor, local): highlights on menu and catalog complicated plain listings -> those pages need all products only -> reverted both controllers and views, kept home and dashboards. Test: ProductHighlightsTest
- 2026-11-27 (feat, local): highlights existed only on home -> dashboards, menu and catalog lacked specials and featured sections -> added shared partial and wired all audiences. Test: ProductHighlightsTest
