<?php

declare(strict_types=1);

use app\services\Navigation;

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ReportFilter $filter */
/** @var array<int|string, string> $customerOptions */
/** @var list<array{label: string, attribute: string, type?: string}> $columns */
/** @var list<array<string, mixed>> $rows */
/** @var array<string, string> $summary */
/** @var list<array{status: string, orders: int}> $statusRows */
/** @var list<string> $statusLabels */
/** @var list<int> $statusValues */
/** @var list<array{date: string, orders: int}> $volumeRows */
/** @var list<string> $volumeLabels */
/** @var list<int> $volumeValues */

$this->title = 'Order report';
$this->params['breadcrumbs'][] = ['label' => 'Reports', 'url' => Navigation::url(Navigation::REPORT_SALES)];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Reports</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
</div>

<?= $this->render('_filters', [
    'filter' => $filter,
    'customerOptions' => $customerOptions,
    'orderFilters' => true,
]) ?>
<?= $this->render('_export_links', ['report' => 'orders', 'filter' => $filter]) ?>
<?= $this->render('_summary_cards', ['summary' => $summary]) ?>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-5">
        <?= $this->render('_chart', [
            'id' => 'orders-status-distribution',
            'type' => 'doughnut',
            'title' => 'Order status distribution',
            'subtitle' => 'Share of orders by workflow status',
            'labels' => $statusLabels,
            'datasets' => [[
                'label' => 'Orders',
                'data' => $statusValues,
                'backgroundColor' => ['#c98c5d', '#a96a3d', '#89431c', '#0d6efd', '#56805d', '#a44a3f'],
            ]],
            'options' => ['plugins' => ['legend' => ['display' => true, 'position' => 'bottom']]],
        ]) ?>
    </div>
    <div class="col-12 col-xl-7">
        <?= $this->render('_chart', [
            'id' => 'orders-volume-trend',
            'type' => 'line',
            'title' => 'Order volume trend',
            'subtitle' => 'Orders created per day',
            'labels' => $volumeLabels,
            'datasets' => [[
                'label' => 'Orders',
                'data' => $volumeValues,
                'borderColor' => '#56805d',
                'backgroundColor' => 'rgba(86, 128, 93, .15)',
                'fill' => true,
                'tension' => 0.3,
            ]],
        ]) ?>
    </div>
</div>

<section class="card cafebara-dashboard-card">
    <div class="card-header">
        <h2 class="h5 mb-0">Orders</h2>
        <p class="small text-body-secondary mb-0">
            Status breakdown:
            <?php if ($statusRows === []) : ?>
                none
            <?php else : ?>
                <?= Html::encode(implode(', ', array_map(
                    static fn(array $row): string => ucfirst($row['status']) . ': ' . $row['orders'],
                    $statusRows,
                ))) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="card-body p-0">
        <?= $this->render('_table', [
            'columns' => $columns,
            'rows' => $rows,
            'emptyMessage' => 'No orders match the selected filters.',
            'pageParam' => 'ordersPage',
        ]) ?>
    </div>
</section>
