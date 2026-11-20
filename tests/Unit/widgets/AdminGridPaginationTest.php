<?php

declare(strict_types=1);

namespace app\tests\Unit\widgets;

use app\widgets\AdminGridView;
use Codeception\Test\Unit;
use Yii;
use yii\data\ArrayDataProvider;

final class AdminGridPaginationTest extends Unit
{
    public function testPaginationKeepsSearchAndSortAcrossPages(): void
    {
        Yii::$app->request->setScriptUrl('/index.php');
        Yii::$app->request->setUrl('/index.php');
        $provider = new ArrayDataProvider([
            'allModels' => array_map(static fn(int $id): array => ['id' => $id], range(1, 25)),
            'pagination' => [
                'pageSize' => 9,
                'pageSizeLimit' => [9, 9],
                'params' => ['page' => 2, 'q' => 'coffee', 'sort' => '-name', 'per-page' => 100],
                'route' => 'category/index',
            ],
        ]);

        self::assertCount(10, $provider->getModels());
        $html = AdminGridView::widget([
            'dataProvider' => $provider,
            'columns' => ['id'],
        ]);
        self::assertStringContainsString('Previous', $html);
        self::assertStringContainsString('Next', $html);
        self::assertStringContainsString('q=coffee', $html);
        self::assertStringContainsString('sort=-name', $html);

        $provider->pagination->setPage(2);
        $provider->refresh();
        self::assertCount(5, $provider->getModels());
    }

    public function testGridUsesSharedHeaderAndSortableColumnLinks(): void
    {
        Yii::$app->request->setScriptUrl('/index.php');
        Yii::$app->request->setUrl('/index.php');
        $provider = new ArrayDataProvider([
            'allModels' => [
                ['id' => 1, 'name' => 'Coffee'],
                ['id' => 2, 'name' => 'Tea'],
            ],
            'pagination' => false,
            'sort' => [
                'attributes' => ['name'],
                'defaultOrder' => ['name' => SORT_ASC],
                'route' => 'category/index',
            ],
        ]);

        $html = AdminGridView::widget([
            'dataProvider' => $provider,
            'columns' => ['id', 'name'],
        ]);

        self::assertStringContainsString('cafebara-table-header', $html);
        self::assertStringContainsString('class="asc"', $html);
        self::assertStringContainsString('data-sort="-name"', $html);
    }

    public function testTimestampColumnRendersFriendlyFormat(): void
    {
        Yii::$app->request->setScriptUrl('/index.php');
        Yii::$app->request->setUrl('/index.php');
        $provider = new ArrayDataProvider([
            'allModels' => [['id' => 1, 'created_at' => 1700000000]],
            'pagination' => false,
        ]);

        $html = AdminGridView::widget([
            'dataProvider' => $provider,
            'columns' => ['created_at:datetime'],
        ]);

        self::assertStringNotContainsString('1700000000', $html);
        self::assertStringContainsString('2023', $html);
        // 1700000000 is 2023-11-14 22:13 UTC, i.e. 2023-11-15 06:13 Asia/Manila (12-hour).
        self::assertStringContainsString('6:13 AM', $html);
    }
}
