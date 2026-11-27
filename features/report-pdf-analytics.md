---
slug: report-pdf-analytics
title: Report PDF analytics
kind: feature
domain: reports
status: active
summary: PDF exports embed browser-captured Chart.js images and summary statistics for every report.
aliases: []
entry:
  - POST /reports/export
  - GET /reports/export?report=sales&format=pdf
files:
  - assets/ReportChartExportAsset.php
  - controllers/ReportController.php
  - services/ReportExportService.php
  - views/report/_export_links.php
  - views/report/_export_pdf.php
  - web/js/report-export.js
tests:
  - tests/Unit/Frontend/ReportChartExportTest.php
  - tests/Unit/Services/ReportExportServiceTest.php
data: []
config: [Navigation::REPORT_EXPORT_INDEX]
connections:
  - report-filters: parent: shares ReportController::build and ReportFilter for export params.
  - report-charts: captures the canvases rendered by the shared chart partial.
hubs: []
updated: 2026-11-27
---

# Report PDF analytics

## Summary

PDF exports embed PNGs captured from the report page’s real Chart.js canvases. The export form posts the images with the current filter; Dompdf places them beside the summary statistics and data table.

## Example use

Open http://localhost:8080/reports/sales and click **Export PDF**.

`vendor/bin/codecept run Unit --filter 'ReportChartExportTest|ReportExportServiceTest'`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| POST /reports/export | `ReportController::actionExport` | http-route, reports.export | Receives chart PNGs and the filter in the action URL. |
| GET /reports/export?report=sales&format=pdf | `ReportController::actionExport` | http-route, reports.export | Non-JS fallback; PDF shows a capture hint instead of charts. |

## Chain

- entry: `views/report/_export_links.php` renders a CSRF-protected POST form.
- logic: `report-export.js` captures each visible Chart.js canvas with `toDataURL`.
- data: `ReportController::chartImages` validates PNG data URLs and size caps.
- presentation: `views/report/_export_pdf.php` embeds images with the summary and table.
- config: `Navigation::REPORT_EXPORT_INDEX` supplies the pretty export URL.

## Flow

1. A report page registers `ReportChartExportAsset`.
2. The shared export partial renders CSV/Excel links and a POST PDF form.
3. The form action carries the report key and current `ReportFilter` attributes.
4. On submit, the script finds `.cafebara-report-chart canvas` elements.
5. Print-safe axis colors are applied, each canvas becomes a PNG data URL, and colors are restored.
6. `ReportController::chartImages` accepts only valid, bounded PNG data URLs.
7. `ReportExportService::pdf` renders the images into the PDF with the summary statistics.

## Gotchas

- Dompdf cannot execute Chart.js; the browser must hand off a rendered PNG.
- Dark-mode text is switched to `#212529` for capture, then restored on the page.
- GET/export clients without JavaScript get the table and a short capture hint, not chart images.
- PNG data URLs are capped at 1.2 MB each and 4 MB total per export.
- Captured charts are bitmap images, not selectable or editable inside the PDF.

## Tests

The focused command checks the export form, capture script, image validation hook and PDF template. Live checks confirm PDFs still return HTTP 200 with summary text.

## Changelog

- 2026-11-27 (fix, local): loyalty export bypassed its permission -> shared export checked only report permissions -> actionExport requires `loyalty.report`. Test: RbacPermissionPolicyTest
- 2026-11-27 (feat, local): exported PDFs showed hand-drawn CSS bars instead of the web charts -> Dompdf cannot run Chart.js -> capture real canvases in the browser and embed validated PNGs. Test: ReportChartExportTest
- 2026-11-27 (feat, local): PDF downloads listed raw rows only -> reports had no printable analytics -> added server-rendered chart bars and summary statistics to every PDF export. Test: ReportExportServiceTest
