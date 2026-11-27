---
slug: hub-best-sellers
title: Best sellers panel
kind: hub
domain: public
status: active
summary: Shared ranked best-sellers panel contract for home, menu, catalog and dashboards.
implicit: [public, staff, admin, customer]
exposed_as: [shared best-sellers panel]
files:
  - views/shared/_best_sellers.php
  - web/css/cafebara-theme.css
tests:
  - tests/Unit/Frontend/BestSellersTest.php
updated: 2026-11-27
---

# Best sellers panel

## Summary

One partial renders the most-ordered active products on home, the public menu, the catalog and all three dashboards. Every row is a link to the product view with a rank chip, product image or placeholder, name and units sold.

## Contract

- Rows come from `ProductReportService::bestSellers` with `product_id`, `product_name` and `quantity`.
- Rank is `#` plus the optional `top_sellers_rank`, falling back to the list index plus one.
- The rank chip carries `data-rank` so the top entry can use primary subtle tokens.
- Links use `Navigation::PRODUCT_VIEW`; image URLs come from `Product::getImageUrl` with a cup placeholder.
- Colors and borders use Bootstrap tokens so both color modes stay readable.

## Example use

http://localhost:8080/

http://localhost:8080/menu

`vendor/bin/codecept run Unit --filter BestSellersTest`

## Gotchas

- `bestSellers()` does not populate `top_sellers_rank`; the index fallback is what produces `#1`, `#2` today.
- The panel is shared by six screens, so a markup or CSS change shows up everywhere.
- Keep the empty state `No completed orders yet.`; it is asserted by existing page tests.

## Changelog

- 2026-11-27 (feat, local): best-seller rows showed no visible rank -> the service has no rank field -> derived `#1..#5` from list order and styled themed rank chips. Test: BestSellersTest
