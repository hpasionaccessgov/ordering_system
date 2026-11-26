<?php

declare(strict_types=1);

use app\services\Navigation;

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ReportFilter $filter */
/** @var list<array{label: string, attribute: string, type?: string}> $columns */
/** @var list<array<string, mixed>> $rows */
/** @var array<string, string> $summary */
/** @var list<string> $consumptionLabels */
/** @var list<int> $consumptionValues */
/** @var list<string> $levelLabels */
/** @var list<int> $levelValues */
/** @var list<int> $minimumValues */

$this->title = 'Inventory report';
$this->params['breadcrumbs'][] = ['label' => 'Reports', 'url' => Navigation::url(Navigation::REPORT_SALES)];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Reports</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
</div>

<?= $this->render('_filters', ['filter' => $filter, 'orderFilters' => false]) ?>
<?= $this->render('_export_links', ['report' => 'inventory', 'filter' => $filter]) ?>
<?= $this->render('_summary_cards', ['summary' => $summary]) ?>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-6">
        <?= $this->render('_chart', [
            'id' => 'inventory-consumption',
            'type' => 'bar',
            'title' => 'Inventory consumption',
            'subtitle' => 'Units deducted by completed orders in the selected range',
            'labels' => $consumptionLabels,
            'datasets' => [[
                'label' => 'Units consumed',
                'data' => $consumptionValues,
                'backgroundColor' => '#c98c5d',
                'borderRadius' => 4,
            ]],
        ]) ?>
    </div>
    <div class="col-12 col-xl-6">
        <?= $this->render('_chart', [
            'id' => 'inventory-stock-levels',
            'type' => 'bar',
            'title' => 'Current stock levels',
            'subtitle' => 'Active items against their minimum thresholds',
            'labels' => $levelLabels,
            'datasets' => [
                [
                    'label' => 'Current stock',
                    'data' => $levelValues,
                    'backgroundColor' => '#56805d',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Minimum stock',
                    'data' => $minimumValues,
                    'backgroundColor' => '#a44a3f',
                    'borderRadius' => 4,
                ],
            ],
            'options' => ['plugins' => ['legend' => ['display' => true, 'position' => 'bottom']]],
        ]) ?>
    </div>
</div>

<section class="card cafebara-dashboard-card">
    <div class="card-header"><h2 class="h5 mb-0">Fast moving items</h2></div>
    <div class="card-body p-0">
        <?= $this->render('_table', [
            'columns' => $columns,
            'rows' => $rows,
            'emptyMessage' => 'No inventory deductions in the selected range.',
        ]) ?>
    </div>
</section>
