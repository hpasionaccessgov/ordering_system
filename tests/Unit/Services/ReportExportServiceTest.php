<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use Codeception\Test\Unit;
use Yii;
use yii\console\Application;
use yii\web\View;

final class ReportExportServiceTest extends Unit
{
    protected function setUp(): void
    {
        if (Yii::$app === null) {
            new Application([
                'id' => 'export-test',
                'basePath' => dirname(__DIR__, 3),
                'components' => [
                    'formatter' => [
                        'class' => \yii\i18n\Formatter::class,
                        'timeZone' => 'Asia/Manila',
                    ],
                ],
            ]);
        }
        parent::setUp();
    }

    public function testPdfTemplateRendersChartBarsBesideSummaryStatistics(): void
    {
        $html = $this->pdfHtml([
            [
                'title' => 'Revenue by day',
                'labels' => ['2026-11-26', '2026-11-27'],
                'values' => [100.0, 250.0],
                'format' => 'money',
            ],
        ]);

        self::assertStringContainsString('Revenue by day', $html);
        self::assertStringContainsString('chart-fill', $html);
        self::assertStringContainsString('width: 100.00%', $html);
        self::assertStringContainsString('PHP 250.00', $html);
        self::assertStringContainsString('Total revenue', $html);
    }

    public function testEmptyChartShowsPlaceholder(): void
    {
        $html = $this->pdfHtml([
            ['title' => 'Orders by day', 'labels' => [], 'values' => []],
        ]);

        self::assertStringContainsString('Orders by day', $html);
        self::assertStringContainsString('No chart data for this series.', $html);
    }

    public function testExportActionPassesChartsToTheService(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__, 3) . '/controllers/ReportController.php');

        self::assertStringContainsString("\$data['charts'] ?? []", $controller);
    }

    /**
     * @param list<array{title: string, labels: list<string>, values: list<float>, format?: string}> $charts
     */
    private function pdfHtml(array $charts): string
    {
        return (new View())->renderFile(Yii::getAlias('@app/views/report/_export_pdf.php'), [
            'title' => 'Sales report',
            'generatedAt' => 'Nov 27, 2026 12:00 PM',
            'filterLabel' => 'This week',
            'summary' => ['Total revenue' => 'PHP 1,000.00'],
            'columns' => [['label' => 'Date', 'attribute' => 'date']],
            'rows' => [['date' => '2026-11-27']],
            'charts' => $charts,
        ]);
    }
}
