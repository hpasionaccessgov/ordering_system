<?php

use app\assets\ReportChartExportAsset;
use app\models\ReportFilter;
use app\services\Navigation;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $report */
/** @var ReportFilter $filter */

ReportChartExportAsset::register($this);
$exportRoute = Navigation::url(Navigation::REPORT_EXPORT_INDEX);
$params = [
    'report' => $report,
    'ReportFilter' => $filter->attributes,
];
?>
<div class="d-flex flex-wrap gap-2 mb-4">
    <?php foreach (['csv' => 'Export CSV', 'excel' => 'Export Excel'] as $format => $label) : ?>
        <?= Html::a($label, array_merge($exportRoute, $params, ['format' => $format]), ['class' => 'btn btn-outline-secondary']) ?>
    <?php endforeach; ?>
    <?= Html::beginForm(array_merge($exportRoute, $params, ['format' => 'pdf']), 'post', [
        'data-report-export-form' => '1',
    ]) ?>
        <?= Html::hiddenInput('report', $report) ?>
        <?= Html::hiddenInput('format', 'pdf') ?>
        <?php foreach ($filter->attributes as $name => $value) : ?>
            <?= Html::hiddenInput("ReportFilter[$name]", $value) ?>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-outline-secondary">Export PDF</button>
    <?= Html::endForm() ?>
</div>
