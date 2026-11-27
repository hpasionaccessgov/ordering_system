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
  - views/product/_form.php
  - views/site/index.php
  - views/customer/index.php
  - views/staff/index.php
  - views/admin/index.php
tests:
  - tests/Unit/Frontend/ProductHighlightsTest.php
  - tests/Unit/Frontend/ProductEditorFlagsTest.php
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

- entry: controllers load active special and featured products with relations; the product editor keeps the two flags exclusive.
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

- The editor disables the other highlight checkbox when one is checked; legacy rows with both flags still need `batchFormId => null` on the featured card to avoid a duplicate input.
- Guests can view products but cannot use batch add; keep the batch form permission-gated.
- Dashboard permission checks still decide whether the highlights render at all.
- `/menu` and `/catalog/products` deliberately list all products; do not add the highlights partial there.
- Home must pass `featuredProducts`; passing only `featuredDataProvider` renders the featured heading without cards.

## Tests

The focused command checks the shared partial, the four showcasing views and the absence of the partial on menu/catalog. The editor test covers flag exclusivity and checkbox color; the customer dashboard test covers the permission guard; the batch-cart test covers a product that is both special and featured.

## Changelog

- 2026-11-27 (fix, local): both flags could be checked and checkboxes turned green -> no exclusion and is-valid won -> editor disables the other and keeps brown. Test: ProductEditorFlagsTest
- 2026-11-27 (fix, local): home showed the featured heading with no cards -> actionIndex passed only the data provider -> passed its models as featuredProducts. Test: ProductHighlightsTest
- 2026-11-27 (refactor, local): menu and catalog had highlights -> plain listings wanted all products -> reverted those pages, kept home and dashboards. Test: ProductHighlightsTest
- 2026-11-27 (feat, local): highlights existed only on home -> other screens lacked specials -> added shared partial to home and dashboards. Test: ProductHighlightsTest
