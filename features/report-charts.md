---
slug: report-charts
title: Report charts
kind: feature
domain: reports
status: active
summary: Shared Chart.js builder renders report charts with theme-aware labels.
aliases: []
entry:
  - screen /reports/sales
files:
  - views/report/_chart.php
tests:
  - tests/Unit/Frontend/ThemeContrastTest.php
data: []
config: [Navigation::REPORT_SALES, Navigation::REPORT_PRODUCT, Navigation::REPORT_ORDERS, Navigation::REPORT_INVENTORY, Navigation::REPORT_LOYALTY]
connections: []
hubs:
  - hub-theme: Chart defaults read --bs-body-color from the document root.
updated: 2026-11-27
---

# Report charts

## Summary

Every report page uses one partial to register Chart.js and build a chart from labels and datasets. Tick and legend text must follow the active color mode instead of Chart.js's fixed gray default.

## Example use

http://localhost:8080/reports/sales

`vendor/bin/codecept run Unit --filter ThemeContrastTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| screen /reports/sales | `ReportController::actionSales` | screen, report.view | Includes the shared chart partial. |
| screen /reports/loyalty | `ReportController::actionLoyalty` | screen, report.view | Uses the same chart builder. |

## Chain

- entry: report actions pass labels and datasets to their views.
- logic: `views/report/_chart.php` merges caller options with chart defaults.
- data: report queries supply labels and numeric series.
- presentation: Chart.js draws into the report canvas.
- config: `ChartJsAsset` registers the library.

## Flow

1. A report action renders its view with prepared series.
2. The view includes `views/report/_chart.php`.
3. The partial registers `ChartJsAsset`.
4. The partial merges responsive defaults with caller options.
5. Inline JavaScript sets `Chart.defaults.color` from `--bs-body-color`.
6. `new Chart` draws the canvas with theme-aware text.

## Gotchas

- Chart.js defaults to `#666` for ticks and legends when no color is supplied.
- Series colors in report views are decorative and do not need dark overrides.
- CSS asset changes can stay cached; hard-refresh before visual checks.

## Tests

The focused test checks that the chart partial reads `--bs-body-color` from the document root. It does not render a canvas in a browser.

## Changelog

- 2026-11-27 (fix, local): chart labels stayed gray in dark mode -> Chart.js default color was unchanged -> chart defaults now read --bs-body-color. Test: ThemeContrastTest
