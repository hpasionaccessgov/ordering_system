---
slug: report-filters
title: Report filters
kind: feature
domain: reports
status: active
summary: Shared report filter normalizes typed customer IDs and defaults report ranges to this week.
aliases: []
entry:
  - screen /reports/orders
  - screen /reports/sales
  - screen /reports/product-performance
  - screen /reports/inventory
  - screen /reports/loyalty
files:
  - controllers/ReportController.php
  - models/ReportFilter.php
  - views/report/_filters.php
tests:
  - tests/Unit/Services/ReportFilterTest.php
data: []
config: [Navigation::REPORT_SALES, Navigation::REPORT_PRODUCT, Navigation::REPORT_ORDERS, Navigation::REPORT_INVENTORY, Navigation::REPORT_LOYALTY]
connections:
  - report-pdf-analytics: sub-feature: PDF exports reuse these filters and ReportController::build data.
hubs:
  - hub-navigation: Report links and form actions use Navigation report keys.
updated: 2026-11-27
---

# Report filters

## Summary

Every report page shares one GET filter form for date preset, custom dates, status and customer. The model normalizes the customer dropdown before Yii assigns it to a typed integer property, and new report pages start on the current week while Today remains an option.

## Example use

http://localhost:8080/reports/orders

http://localhost:8080/reports/sales

`vendor/bin/codecept run Unit --filter ReportFilterTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| screen /reports/orders | `ReportController::actionOrders` | screen, reports.view | Includes customer and status filters. |
| screen /reports/sales | `ReportController::actionSales` | screen, reports.view | Uses the shared date preset. |
| GET report export | `ReportController::actionExport` | http-route, reports.export | Reuses the same filter model. |

## Chain

- entry: `ReportController::filter` loads query params into `ReportFilter`.
- logic: `ReportFilter::setCustomerId` converts dropdown values to `int` or `null`.
- data: `ReportFilter::range` and report services apply the selected range and customer.
- presentation: `views/report/_filters.php` renders the shared GET form.
- config: Navigation report keys generate report links and form actions.

## Flow

1. A report action calls `ReportController::filter`.
2. `Model::load` passes raw query values to `ReportFilter` setters.
3. `setCustomerId` maps an empty dropdown to `null` and numeric IDs to integers.
4. `validate` checks preset, dates, status and customer existence.
5. `range` resolves the preset; the default preset is `week`.
6. The report service applies the filter and the view highlights the selected values.

## Gotchas

- `customerId` is typed `?int`; loading the empty dropdown option directly into it throws before validation.
- `load()` invokes `setCustomerId`, so fix belongs on the model, not only in the orders action.
- `SalesReportService::todaySummary()` sets `preset = today` explicitly and is unaffected by the new default.
- Today stays in the preset dropdown; only the initial selection changed.

## Tests

The focused unit test covers the week default, Monday start and customer dropdown normalization from an empty or numeric string.

## Changelog

- 2026-11-27 (feat, local): PDF exports had no analytics payload -> report builders returned only table data -> added chart series to `ReportController::build`. Test: ReportExportServiceTest
- 2026-11-27 (fix, local): report day counts were one too high -> inclusive end-of-day was rounded instead of truncated -> `days()` now uses `intdiv`. Test: ReportFilterTest
- 2026-11-27 (fix, local): filtering orders threw on an empty customer dropdown -> Yii assigned `''` to a typed `?int` property -> normalized the value in `ReportFilter::setCustomerId` and defaulted presets to week. Test: ReportFilterTest
