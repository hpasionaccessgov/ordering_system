---
slug: product-report-ranking
title: Product report ranking
kind: feature
domain: reports
status: active
summary: Product report ranks completed orders in range, matching catalog best sellers.
aliases: []
entry:
  - screen /reports/product-performance
files:
  - services/ProductReportService.php
  - tests/Unit/Services/ProductReportRankingTest.php
tests:
  - tests/Unit/Services/ProductReportRankingTest.php
data: [orders.status, order_items.quantity]
config: []
connections:
  - report-tables: ranking and least rows feed the shared report table.
  - report-pdf-analytics: exports reuse the same build rows and summary.
hubs:
  - hub-best-sellers: bestSellers is the completed-only count contract.
updated: 2026-11-27
---

# Product report ranking

## Summary

The product-performance report ranks products by completed-order units inside the selected date range. Cancelled and pending orders never inflate the top card, table, charts or exports.

## Example use

http://localhost:8080/reports/product-performance

`vendor/bin/codecept run Unit --filter ProductReportRankingTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| screen /reports/product-performance | `ReportController::actionProduct` | screen, reports.view | Builds rows through `ProductReportService`. |

## Chain

- entry: `ReportController::build` calls `ProductReportService::ranking` and `least`.
- logic: both queries filter `orders.status` to completed inside the date range.
- data: `order_items.quantity` supplies sold units; `order_items.subtotal` supplies revenue.
- presentation: the report card, table, chart and export render the same rows.
- config: no new config source; `ReportFilter::range` supplies the range.

## Flow

1. The product report action builds a `ReportFilter` and calls `ReportController::build`.
2. `ProductReportService::ranking` restricts completed orders by `created_at`.
3. `ProductReportService::least` applies the same status to its sales subquery.
4. Both methods normalize quantities and revenue to numbers.
5. The view renders the top product card, charts and paginated table.
6. Exports reuse the prepared rows without recomputing counts.

## Gotchas

- The report date range is based on `orders.created_at`, not `completed_at`.
- Cancelled units are excluded even if they were created inside the range.
- `bestSellers` is all-time and active-product only; the report is range-based.
- Changing this service also changes CSV, Excel and PDF numbers.

## Tests

The focused test asserts that both report queries include the completed-status filter. Live QA compares Brown Sugar's report count with the catalog best-sellers count.

## Changelog

- 2026-11-27 (fix, local): report showed 22 sold while catalog showed 21 -> ranking counted cancelled orders -> report queries now filter to completed orders. Test: ProductReportRankingTest
