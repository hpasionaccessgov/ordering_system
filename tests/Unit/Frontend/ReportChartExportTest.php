<?php

declare(strict_types=1);

namespace app\tests\Unit\Frontend;

use Codeception\Test\Unit;

final class ReportChartExportTest extends Unit
{
    private function source(string $path): string
    {
        return (string) file_get_contents(dirname(__DIR__, 3) . '/' . $path);
    }

    public function testPdfExportPostsTheCurrentFilterFromTheSharedLinksPartial(): void
    {
        $links = $this->source('views/report/_export_links.php');

        self::assertStringContainsString('ReportChartExportAsset::register($this)', $links);
        self::assertStringContainsString('Navigation::REPORT_EXPORT_INDEX', $links);
        self::assertStringContainsString("'data-report-export-form' => '1'", $links);
        self::assertStringContainsString("Html::hiddenInput(\"ReportFilter[\$name]\", \$value)", $links);
        self::assertStringContainsString("Html::beginForm(array_merge(\$exportRoute, \$params, ['format' => 'pdf']), 'post'", $links);
    }

    public function testExportAssetLoadsTheCaptureScriptAfterChartJs(): void
    {
        $asset = $this->source('assets/ReportChartExportAsset.php');

        self::assertStringContainsString("'js/report-export.js'", $asset);
        self::assertStringContainsString('ChartJsAsset::class', $asset);
    }

    public function testCaptureScriptCopiesRenderedCanvasesIntoTheExportForm(): void
    {
        $script = $this->source('web/js/report-export.js');

        self::assertStringContainsString(".cafebara-report-chart canvas", $script);
        self::assertStringContainsString("canvas.toDataURL('image/png')", $script);
        self::assertStringContainsString("input.name = 'charts[]'", $script);
        self::assertStringContainsString("node[key] = '#212529'", $script);
        self::assertStringContainsString('maxTotalLength', $script);
    }
}
