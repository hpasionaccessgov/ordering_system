<?php

declare(strict_types=1);

use app\models\ApplicationSettings;
use app\services\ReportFormat;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/** @var list<array{label: string, attribute: string, type?: string}> $columns */
/** @var list<array<string, mixed>> $rows */
/** @var string $emptyMessage */
/** @var string $pageParam */

$pageParam ??= 'page';
$provider = new ArrayDataProvider([
    'allModels' => $rows,
    'sort' => false,
    'pagination' => array_merge(ApplicationSettings::pagination(), ['pageParam' => $pageParam]),
]);
?>
<div class="table-responsive cafebara-table-scroll">
    <table class="table table-striped table-hover table-bordered align-middle mb-0 cafebara-data-table">
        <thead>
            <tr class="cafebara-table-header">
                <?php foreach ($columns as $column) : ?>
                    <th scope="col"><?= Html::encode($column['label']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($provider->count === 0) : ?>
                <tr>
                    <td colspan="<?= count($columns) ?>" class="text-center text-body-secondary py-4">
                        <?= Html::encode($emptyMessage ?? 'No data for the selected filters.') ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($provider->getModels() as $row) : ?>
                    <tr>
                        <?php foreach ($columns as $column) : ?>
                            <td><?= Html::encode(ReportFormat::value($row, $column)) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($provider->pagination->pageCount > 1) : ?>
    <nav class="px-3 py-3 border-top" aria-label="Report table pages">
        <?= LinkPager::widget([
            'pagination' => $provider->pagination,
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
            'options' => ['class' => 'pagination pagination-sm mb-0 justify-content-center'],
        ]) ?>
    </nav>
<?php endif; ?>
