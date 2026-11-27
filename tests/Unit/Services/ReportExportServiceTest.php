<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\controllers\ReportController;
use Codeception\Test\Unit;
use ReflectionMethod;
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

    public function testPdfTemplateEmbedsCapturedChartImagesBesideSummaryStatistics(): void
    {
        $html = $this->pdfHtml(['data:image/png;base64,AAEC']);

        self::assertStringContainsString('Visual analytics', $html);
        self::assertStringContainsString('class="chart-image"', $html);
        self::assertStringContainsString('src="data:image/png;base64,AAEC"', $html);
        self::assertStringContainsString('Total revenue', $html);
        self::assertStringContainsString('2026-11-27', $html);
    }

    public function testMissingChartImagesShowCaptureHint(): void
    {
        $html = $this->pdfHtml([]);

        self::assertStringContainsString('Charts are captured from the report page', $html);
        self::assertStringNotContainsString('class="chart-image"', $html);
    }

    public function testExportActionValidatesCapturedCharts(): void
    {
        $controller = (string) file_get_contents(dirname(__DIR__, 3) . '/controllers/ReportController.php');

        self::assertStringContainsString("data:image\\/png;base64", $controller);
        self::assertStringContainsString('str_starts_with($binary', $controller);
        self::assertStringContainsString('post(\'charts\', [])', $controller);
    }

    public function testOnlyPngDataUrlsReachThePdf(): void
    {
        $valid = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==';
        $controller = new ReportController('report', Yii::$app);
        $method = new ReflectionMethod($controller, 'chartImages');

        $images = $method->invoke($controller, [
            'not-a-data-url',
            'data:image/png;base64,not!valid',
            'data:image/gif;base64,R0lGODlhAQABAAAAACw=',
            $valid,
        ]);

        self::assertSame([$valid], $images);
    }

    /**
     * @param list<string> $chartImages
     */
    private function pdfHtml(array $chartImages): string
    {
        return (new View())->renderFile(Yii::getAlias('@app/views/report/_export_pdf.php'), [
            'title' => 'Sales report',
            'generatedAt' => 'Nov 27, 2026 12:00 PM',
            'filterLabel' => 'This week',
            'summary' => ['Total revenue' => 'PHP 1,000.00'],
            'columns' => [['label' => 'Date', 'attribute' => 'date']],
            'rows' => [['date' => '2026-11-27']],
            'chartImages' => $chartImages,
        ]);
    }
}
