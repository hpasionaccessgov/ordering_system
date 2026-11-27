<?php

declare(strict_types=1);

namespace app\services;

use app\models\ReportFilter;
use Dompdf\Dompdf;
use Dompdf\Options;
use Yii;
use yii\web\Response;
use yii\web\View;

/**
 * Streams the same filtered report data as CSV, Excel 2003 XML or PDF.
 */
final class ReportExportService
{
    /**
     * @param array<string, string>|list<array{label: string, value: string}> $summary
     * @param list<array{label: string, attribute: string, type?: string}> $columns
     * @param list<array<string, mixed>> $rows
     * @param list<array{title: string, labels: list<string>, values: list<float>, format?: string}> $charts
     */
    public function export(
        string $format,
        string $title,
        array $columns,
        array $rows,
        array $summary,
        ReportFilter $filter,
        string $filename,
        array $charts = [],
    ): Response {
        $summary = $this->normalizeSummary($summary);
        return match ($format) {
            'csv' => $this->csv($title, $columns, $rows, $summary, $filter, $filename),
            'excel' => $this->excel($title, $columns, $rows, $summary, $filter, $filename),
            'pdf' => $this->pdf($title, $columns, $rows, $summary, $filter, $filename, $charts),
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }

    /**
     * @param array<string, string> $summary
     * @param list<array{label: string, attribute: string, type?: string}> $columns
     * @param list<array<string, mixed>> $rows
     */
    private function csv(
        string $title,
        array $columns,
        array $rows,
        array $summary,
        ReportFilter $filter,
        string $filename,
    ): Response {
        $lines = [
            [$title],
            ['Generated', $this->generatedAt()],
            ['Filters', $filter->label()],
            [],
        ];
        foreach ($summary as $label => $value) {
            $lines[] = [$label, $value];
        }
        $lines[] = [];
        $lines[] = array_column($columns, 'label');
        foreach ($rows as $row) {
            $lines[] = array_map(
                static fn(array $column): string => ReportFormat::value($row, $column),
                $columns,
            );
        }

        $handle = fopen('php://temp', 'w+');
        foreach ($lines as $line) {
            fputcsv($handle, $line, ',', '"', '\\');
        }
        rewind($handle);
        $content = (string) stream_get_contents($handle);
        fclose($handle);

        return $this->response('text/csv; charset=UTF-8', "\xEF\xBB\xBF" . $content, $filename . '.csv');
    }

    /**
     * @param array<string, string> $summary
     * @param list<array{label: string, attribute: string, type?: string}> $columns
     * @param list<array<string, mixed>> $rows
     */
    private function excel(
        string $title,
        array $columns,
        array $rows,
        array $summary,
        ReportFilter $filter,
        string $filename,
    ): Response {
        $xml = '<?xml version="1.0"?>' . "\n"
            . '<?mso-application progid="Excel.Sheet"?>' . "\n"
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            . 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            . '<Styles>'
            . '<Style ss:ID="Title"><Font ss:Bold="1" ss:Size="14"/></Style>'
            . '<Style ss:ID="Header"><Font ss:Bold="1"/>'
            . '<Interior ss:Color="#F2E7DC" ss:Pattern="Solid"/></Style>'
            . '<Style ss:ID="Date"><NumberFormat ss:Format="yyyy-mm-dd"/></Style>'
            . '<Style ss:ID="Number"><NumberFormat ss:Format="#,##0"/></Style>'
            . '<Style ss:ID="Money"><NumberFormat ss:Format="#,##0.00"/></Style>'
            . '</Styles><Worksheet ss:Name="Report"><Table>';

        foreach ($columns as $column) {
            $width = mb_strlen($column['label']);
            foreach ($rows as $row) {
                $width = max($width, mb_strlen(ReportFormat::value($row, $column)));
            }
            $xml .= '<Column ss:Width="' . min(60, max(12, $width + 2)) . '"/>';
        }

        $xml .= $this->xmlRow([$this->xmlCell($title, 'text', 'Title')]);
        $xml .= $this->xmlRow([$this->xmlCell('Generated'), $this->xmlCell($this->generatedAt())]);
        $xml .= $this->xmlRow([$this->xmlCell('Filters'), $this->xmlCell($filter->label())]);
        $xml .= $this->xmlRow([]);
        foreach ($summary as $label => $value) {
            $xml .= $this->xmlRow([$this->xmlCell($label), $this->xmlCell($value)]);
        }
        $xml .= $this->xmlRow([]);
        $xml .= $this->xmlRow(array_map(
            fn(array $column): string => $this->xmlCell($column['label'], 'text', 'Header'),
            $columns,
        ));

        foreach ($rows as $row) {
            $cells = [];
            foreach ($columns as $column) {
                $type = $column['type'] ?? 'text';
                $value = $row[$column['attribute']] ?? '';
                $cells[] = match ($type) {
                    'date' => $this->xmlCell(ReportFormat::value($row, $column), 'date', 'Date'),
                    'number' => $this->xmlCell((string) (0 + $value), 'number', 'Number'),
                    'decimal' => $this->xmlCell((string) (0 + $value), 'number', 'Money'),
                    'money' => $this->xmlCell((string) (0 + $value), 'money', 'Money'),
                    default => $this->xmlCell(ReportFormat::value($row, $column)),
                };
            }
            $xml .= $this->xmlRow($cells);
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return $this->response(
            'application/vnd.ms-excel; charset=UTF-8',
            $xml,
            $filename . '.xls',
        );
    }

    /**
     * @param array<string, string> $summary
     * @param list<array{label: string, attribute: string, type?: string}> $columns
     * @param list<array<string, mixed>> $rows
     */
    private function pdf(
        string $title,
        array $columns,
        array $rows,
        array $summary,
        ReportFilter $filter,
        string $filename,
        array $charts,
    ): Response {
        $html = (new View())->renderFile(Yii::getAlias('@app/views/report/_export_pdf.php'), [
            'title' => $title,
            'generatedAt' => $this->generatedAt(),
            'filterLabel' => $filter->label(),
            'summary' => $summary,
            'columns' => $columns,
            'rows' => $rows,
            'charts' => $charts,
        ]);

        $dompdf = new Dompdf(new Options(['isRemoteEnabled' => false]));
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response('application/pdf', $dompdf->output(), $filename . '.pdf');
    }

    private function generatedAt(): string
    {
        return (string) Yii::$app->formatter->asDatetime(time());
    }

    /**
     * @param list<string> $cells
     */
    private function xmlRow(array $cells): string
    {
        return '<Row>' . implode('', $cells) . '</Row>';
    }

    private function xmlCell(string $value, string $type = 'text', string $style = ''): string
    {
        $styleAttribute = $style === '' ? '' : ' ss:StyleID="' . $style . '"';
        if ($type === 'date') {
            if ($value === '') {
                return '<Cell' . $styleAttribute . '/>';
            }
            $value = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value . 'T00:00:00' : $value;

            return '<Cell' . $styleAttribute . '><Data ss:Type="DateTime">' . $value . '</Data></Cell>';
        }
        if (in_array($type, ['number', 'money'], true)) {
            $value = is_numeric($value) ? (string) (0 + $value) : '0';

            return '<Cell' . $styleAttribute . '><Data ss:Type="Number">' . $value . '</Data></Cell>';
        }

        return '<Cell' . $styleAttribute . '><Data ss:Type="String">'
            . htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8')
            . '</Data></Cell>';
    }

    private function response(string $contentType, string $content, string $filename): Response
    {
        $filename = preg_replace('/[^A-Za-z0-9_.-]+/', '-', $filename) ?: 'report';
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->getHeaders()->set('Content-Type', $contentType);
        $response->getHeaders()->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->getHeaders()->set('Cache-Control', 'no-store');
        $response->content = $content;

        return $response;
    }

    /**
     * @param array<string, string>|list<array{label: string, value: string}> $summary
     * @return array<string, string>
     */
    private function normalizeSummary(array $summary): array
    {
        $normalized = [];
        foreach ($summary as $key => $item) {
            if (is_array($item)) {
                $normalized[(string) ($item['label'] ?? '')] = (string) ($item['value'] ?? '');
                continue;
            }
            $normalized[(string) $key] = (string) $item;
        }

        return $normalized;
    }
}
