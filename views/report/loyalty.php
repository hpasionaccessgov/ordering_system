<?php

declare(strict_types=1);

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ReportFilter $filter */
/** @var list<array{label: string, attribute: string, type?: string}> $columns */
/** @var list<array<string, mixed>> $rows */
/** @var array $summary */
/** @var list<string> $tierLabels */
/** @var list<int> $tierValues */
/** @var list<string> $volumeLabels */
/** @var list<int> $earnedValues */
/** @var list<int> $redeemedValues */

$this->title = 'Loyalty report';
$this->params['breadcrumbs'][] = ['label' => 'Reports', 'url' => ['/reports/sales']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Reports</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
        <p class="mb-0 text-body-secondary">Points issued, redemptions, and member tiers.</p>
    </div>
    <?= Html::a('Loyalty admin', ['/admin/loyalty'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?= $this->render('_filters', ['filter' => $filter, 'orderFilters' => false]) ?>
<?= $this->render('_export_links', ['report' => 'loyalty', 'filter' => $filter]) ?>
<?= $this->render('_summary_cards', ['summary' => $summary]) ?>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-5">
        <?= $this->render('_chart', [
            'id' => 'loyalty-tier-distribution',
            'type' => 'bar',
            'title' => 'Tier distribution',
            'subtitle' => 'Members active in the selected range',
            'labels' => $tierLabels,
            'datasets' => [[
                'label' => 'Members',
                'data' => $tierValues,
                'backgroundColor' => ['#a44a3f', '#6c757d', '#d19a2d'],
            ]],
            'options' => ['plugins' => ['legend' => ['display' => false]]],
        ]) ?>
    </div>
    <div class="col-12 col-xl-7">
        <?= $this->render('_chart', [
            'id' => 'loyalty-points-trend',
            'type' => 'line',
            'title' => 'Points activity',
            'subtitle' => 'Issued and redeemed per day',
            'labels' => $volumeLabels,
            'datasets' => [
                [
                    'label' => 'Issued',
                    'data' => $earnedValues,
                    'borderColor' => '#56805d',
                    'backgroundColor' => 'rgba(86, 128, 93, .15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Redeemed',
                    'data' => $redeemedValues,
                    'borderColor' => '#89431c',
                    'backgroundColor' => 'rgba(137, 67, 28, .12)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ]) ?>
    </div>
</div>

<section class="card cafebara-dashboard-card">
    <div class="card-header">
        <h2 class="h5 mb-0">Daily loyalty activity</h2>
    </div>
    <div class="card-body p-0">
        <?= $this->render('_table', [
            'columns' => $columns,
            'rows' => $rows,
            'emptyMessage' => 'No loyalty activity for the selected filters.',
        ]) ?>
    </div>
</section>
