<?php

use app\models\ReportFilter;
use yii\helpers\Html;

/** @var string $report */
/** @var ReportFilter $filter */
?>
<div class="d-flex flex-wrap gap-2 mb-4">
    <?php foreach (['csv' => 'Export CSV', 'excel' => 'Export Excel', 'pdf' => 'Export PDF'] as $format => $label) : ?>
        <?= Html::a($label, [
            '/reports/export',
            'report' => $report,
            'format' => $format,
            'ReportFilter' => $filter->attributes,
        ], ['class' => 'btn btn-outline-secondary']) ?>
    <?php endforeach; ?>
</div>
