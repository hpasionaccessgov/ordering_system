<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\ReportFilter $filter */
/** @var list<array{label: string, attribute: string, type?: string}> $columns */
/** @var list<array<string, mixed>> $rows */
/** @var array<string, string> $summary */
/** @var list<string> $revenueLabels */
/** @var list<float> $revenueValues */
/** @var list<int> $orderValues */

$this->title = 'Sales report';
$this->params['breadcrumbs'][] = ['label' => 'Reports', 'url' => ['/reports/sales']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Reports</p>
        <h1 class="mb-0 h3"><?= yii\helpers\Html::encode($this->title) ?></h1>
    </div>
</div>

<?= $this->render('_filters', ['filter' => $filter, 'orderFilters' => false]) ?>
<?= $this->render('_export_links', ['report' => 'sales', 'filter' => $filter]) ?>
<?= $this->render('_summary_cards', ['summary' => $summary]) ?>

<div class="row g-4 mb-4">
    <div class="col-12">
        <?= $this->render('_chart', [
            'id' => 'sales-revenue-trend',
            'type' => 'line',
            'title' => 'Revenue trend',
            'subtitle' => 'Completed-order revenue per day',
            'labels' => $revenueLabels,
            'datasets' => [[
                'label' => 'Revenue',
                'data' => $revenueValues,
                'borderColor' => '#89431c',
                'backgroundColor' => 'rgba(137, 67, 28, .15)',
                'fill' => true,
                'tension' => 0.3,
            ]],
        ]) ?>
    </div>
    <div class="col-12">
        <?= $this->render('_chart', [
            'id' => 'sales-orders-trend',
            'type' => 'bar',
            'title' => 'Orders trend',
            'subtitle' => 'All orders created per day, regardless of status',
            'labels' => $revenueLabels,
            'datasets' => [[
                'label' => 'Orders',
                'data' => $orderValues,
                'backgroundColor' => '#56805d',
                'borderRadius' => 4,
            ]],
        ]) ?>
    </div>
</div>

<section class="card cafebara-dashboard-card">
    <div class="card-header"><h2 class="h5 mb-0">Daily breakdown</h2></div>
    <div class="card-body p-0">
        <?= $this->render('_table', [
            'columns' => $columns,
            'rows' => $rows,
            'emptyMessage' => 'No orders in the selected range.',
        ]) ?>
    </div>
</section>
