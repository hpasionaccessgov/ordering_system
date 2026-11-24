<?php

use app\services\ReportFormat;
use yii\helpers\Html;

/** @var list<array{label: string, attribute: string, type?: string}> $columns */
/** @var list<array<string, mixed>> $rows */
/** @var string $emptyMessage */
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
            <?php if ($rows === []) : ?>
                <tr>
                    <td colspan="<?= count($columns) ?>" class="text-center text-body-secondary py-4">
                        <?= Html::encode($emptyMessage ?? 'No data for the selected filters.') ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($rows as $row) : ?>
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
