---
slug: report-pdf-analytics
title: Report PDF analytics
kind: feature
domain: reports
status: active
summary: PDF exports embed server-rendered analytics bars and summary statistics for every report.
aliases: []
entry:
  - GET /reports/export?report=sales&format=pdf
files:
  - controllers/ReportController.php
  - services/ReportExportService.php
  - views/report/_export_pdf.php
tests:
  - tests/Unit/Services/ReportExportServiceTest.php
data: []
config: []
connections:
  - report-filters: parent: shares ReportController::build and ReportFilter for export params.
  - report-charts: PDF series use the same labels and values as the on-screen charts.
hubs: []
updated: 2026-11-27
---

# Report PDF analytics

## Summary

Every PDF export includes the filtered summary statistics plus compact bar charts drawn server-side. Dompdf renders the bars as HTML tables, so exports need no JavaScript or chart image service.

## Example use

http://localhost:8080/reports/export?report=sales&format=pdf

`vendor/bin/codecept run Unit --filter ReportExportServiceTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| GET /reports/export?report=sales&format=pdf | `ReportController::actionExport` | http-route, reports.export | Passes chart payloads into `ReportExportService::export`. |
| GET /reports/export?report=product&format=pdf | `ReportController::actionExport` | http-route, reports.export | Same pipeline for every report type. |

## Chain

- entry: `ReportController::actionExport` builds report data and filter state.
- logic: private `chart` methods cap each series to the last twelve values.
- data: report services supply labels, counts, revenue and stock series.
- presentation: `views/report/_export_pdf.php` renders summary cells and CSS bar tables.
- config: `ReportController::FORMATS` keeps PDF in the export allow-list.

## Flow

1. The export link sends the active `ReportFilter` to `ReportController::actionExport`.
2. `ReportController::build` returns columns, rows, summary and chart series.
3. `ReportController::chart` normalizes labels to strings and values to floats.
4. `ReportExportService::export` routes PDF requests to `pdf`.
5. `ReportExportService::pdf` renders the standalone export template.
6. The template draws one bar per value using the series maximum as 100 percent.
7. `Dompdf::render` produces the attachment and `response` streams it.

## Gotchas

- Chart.js cannot run inside Dompdf; charts must be rendered as HTML/CSS before conversion.
- A series with no positive values shows `No chart data for this series.` instead of a broken chart.
- Money is converted from `₱` to `PHP ` because the PDF font does not include the peso glyph.
- Only the last twelve points of a long series are drawn so bars stay readable.

## Tests

The focused unit test renders the PDF template, checks bar widths, formatted values and the empty-series placeholder. Live export checks all five report PDFs return HTTP 200 with chart headings.

## Changelog

- 2026-11-27 (feat, local): PDF downloads listed raw rows only -> reports had no printable analytics -> added server-rendered chart bars and summary statistics to every PDF export. Test: ReportExportServiceTest
