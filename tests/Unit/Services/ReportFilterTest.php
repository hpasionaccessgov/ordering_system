<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\ReportFilter;
use Codeception\Test\Unit;
use Yii;
use yii\console\Application;

final class ReportFilterTest extends Unit
{
    protected function setUp(): void
    {
        if (Yii::$app === null) {
            new Application([
                'id' => 'report-test',
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

    public function testDefaultPresetIsWeek(): void
    {
        $filter = new ReportFilter();
        self::assertSame('week', $filter->preset);
        [$from] = $filter->range();
        self::assertSame('Monday', Yii::$app->formatter->asDate($from, 'php:l'));
    }

    public function testTodayCoversOneDay(): void
    {
        $filter = new ReportFilter();
        $filter->preset = 'today';
        self::assertSame(1, $filter->days());
    }

    public function testLoadNormalizesCustomerDropdownValue(): void
    {
        $filter = new ReportFilter();
        $filter->load(['ReportFilter' => ['customerId' => '', 'preset' => 'week']]);
        self::assertNull($filter->customerId);

        $filter->load(['ReportFilter' => ['customerId' => '7']]);
        self::assertSame(7, $filter->customerId);
    }

    public function testCustomRangeIsInclusive(): void
    {
        $filter = new ReportFilter();
        $filter->preset = 'custom';
        $filter->from = '2020-01-01';
        $filter->to = '2020-01-03';

        self::assertTrue($filter->validate());
        self::assertSame(3, $filter->days());
        [$from, $to] = $filter->range();
        self::assertLessThan($to, $from);
        self::assertSame('2020-01-03', Yii::$app->formatter->asDate($to, 'php:Y-m-d'));
    }

    public function testInvalidRangeIsRejected(): void
    {
        $filter = new ReportFilter();
        $filter->preset = 'custom';
        $filter->from = '2020-01-03';
        $filter->to = '2020-01-01';

        self::assertFalse($filter->validate());
        self::assertSame(1, $filter->days());
    }
}
