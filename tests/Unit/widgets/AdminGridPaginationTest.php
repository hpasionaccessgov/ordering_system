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
                'pageSize' => 10,
                'pageSizeLimit' => [10, 10],
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
}
