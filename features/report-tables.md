---
slug: report-tables
title: Report tables
kind: feature
domain: reports
status: active
summary: Report tables paginate with the shared settings page size and keep each table's page separate.
aliases: []
entry:
  - screen /reports/sales
  - screen /reports/product-performance
  - screen /reports/orders
  - screen /reports/inventory
  - screen /reports/loyalty
files:
  - views/report/_table.php
  - views/report/sales.php
  - views/report/product.php
  - views/report/orders.php
  - views/report/inventory.php
  - views/report/loyalty.php
tests:
  - tests/Unit/Frontend/ReportTablePaginationTest.php
data: []
config: [ApplicationSettings::pagination]
connections:
  - report-filters: rows come from ReportController::build and filters stay in the query string while paging.
hubs: []
updated: 2026-11-27
---

# Report tables

## Summary

Every report table uses the same page size configured in Settings. Each table has its own page parameter, so paging one table does not move another table on the same report.

## Example use

http://localhost:8080/reports/orders?ordersPage=2

`vendor/bin/codecept run Unit --filter ReportTablePaginationTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| screen /reports/sales | `ReportController::actionSales` | screen, reports.view | Daily breakdown uses `salesPage`. |
| screen /reports/product-performance | `ReportController::actionProduct` | screen, reports.view | Top and least tables use separate page parameters. |
| screen /reports/orders | `ReportController::actionOrders` | screen, reports.view | Orders table uses `ordersPage`. |

## Chain

- entry: report actions pass `rows` from `ReportController::build` to their views.
- logic: `views/report/_table.php` wraps rows in an `ArrayDataProvider`.
- data: `ApplicationSettings::pagination` supplies the configured page size.
- presentation: `LinkPager` renders page links below the table.
- config: each view passes a unique `pageParam` to avoid pager collisions.

## Flow

1. A report action renders its view with prepared rows.
2. The view passes rows and a table-specific `pageParam` to the shared table partial.
3. `_table.php` builds an unpaginated-sort `ArrayDataProvider`.
4. `ApplicationSettings::pagination` fixes the page size to the Settings value.
5. The provider returns only the current page's rows.
6. `LinkPager` renders previous, next and numbered links when more than one page exists.
7. Yii keeps the active report filters in every pager URL.

## Gotchas

- Report rows are arrays, so the shared helper needs `ArrayDataProvider`, not a search model.
- Product performance has two tables on one page; both must have distinct page parameters.
- Exports still receive the full row set; pagination is presentation-only.
- `pageSizeLimit` is locked to the configured value, so query strings cannot override it.

## Tests

The focused test checks the shared provider, pager and every view's page parameter. Live QA confirms the orders table shows exactly the configured number of rows and page links move between pages.

## Changelog

- 2026-11-27 (fix, local): report tables ignored Settings page size -> shared partial rendered every row -> added `ArrayDataProvider` and per-table pagers. Test: ReportTablePaginationTest
