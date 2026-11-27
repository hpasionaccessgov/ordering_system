<?php

declare(strict_types=1);

use app\services\Navigation;

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ReportFilter $filter */
/** @var list<array{label: string, attribute: string, type?: string}> $columns */
/** @var list<array<string, mixed>> $rows */
/** @var array<string, string> $summary */
/** @var list<array<string, mixed>> $topRows */
/** @var list<array<string, mixed>> $leastRows */
/** @var list<string> $topLabels */
/** @var list<int> $topValues */
/** @var list<float> $revenueValues */

$this->title = 'Product performance';
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
<?= $this->render('_export_links', ['report' => 'product', 'filter' => $filter]) ?>
<?= $this->render('_summary_cards', ['summary' => $summary]) ?>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7">
        <?= $this->render('_chart', [
            'id' => 'product-top-ten',
            'type' => 'bar',
            'title' => 'Top 10 products',
            'subtitle' => 'Units sold in the selected range',
            'labels' => $topLabels,
            'datasets' => [[
                'label' => 'Quantity sold',
                'data' => $topValues,
                'backgroundColor' => '#c98c5d',
                'borderRadius' => 4,
            ]],
            'options' => ['indexAxis' => 'y'],
        ]) ?>
    </div>
    <div class="col-12 col-xl-5">
        <?= $this->render('_chart', [
            'id' => 'product-distribution',
            'type' => 'doughnut',
            'title' => 'Product sales distribution',
            'subtitle' => 'Share of units sold by the top products',
            'labels' => $topLabels,
            'datasets' => [[
                'label' => 'Units sold',
                'data' => $topValues,
                'backgroundColor' => ['#89431c', '#c98c5d', '#56805d', '#a96a3d', '#a44a3f', '#7b8f6a', '#d4a373', '#6c584c', '#9c6644', '#b56576'],
            ]],
            'options' => ['plugins' => ['legend' => ['display' => true, 'position' => 'bottom']]],
        ]) ?>
    </div>
</div>

<section class="mb-4 card cafebara-dashboard-card">
    <div class="card-header"><h2 class="mb-0 h5">Top selling products</h2></div>
    <div class="p-0 card-body">
        <?= $this->render('_table', [
            'columns' => $columns,
            'rows' => $topRows,
            'pageParam' => 'topProductsPage',
        ]) ?>
    </div>
</section>

<section class="card cafebara-dashboard-card">
    <div class="card-header"><h2 class="mb-0 h5">Least selling products</h2></div>
    <div class="p-0 card-body">
        <?= $this->render('_table', [
            'columns' => $columns,
            'rows' => $leastRows,
            'emptyMessage' => 'No active products.',
            'pageParam' => 'leastProductsPage',
        ]) ?>
    </div>
</section>
