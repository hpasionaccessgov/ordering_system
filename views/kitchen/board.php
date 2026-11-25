<?php

declare(strict_types=1);

use app\models\Order;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array<string, list<Order>> $columns */
/** @var array<string, int> $counts */
/** @var int $completedToday */
/** @var float|null $averagePreparationMinutes */
/** @var list<string> $statuses */

$this->title = 'Kitchen Order Board';
$this->params['breadcrumbs'][] = $this->title;

$columnMeta = [
    Order::STATUS_PENDING => ['label' => 'Pending', 'class' => 'text-bg-secondary'],
    Order::STATUS_CONFIRMED => ['label' => 'Confirmed', 'class' => 'text-bg-info'],
    Order::STATUS_PROCESSING => ['label' => 'Processing', 'class' => 'text-bg-primary'],
    Order::STATUS_READY_FOR_PICKUP => ['label' => 'Ready For Pickup', 'class' => 'text-bg-success'],
];
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Staff Operations</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="text-body-secondary small">
        Completed today: <strong><?= Html::encode((string) $completedToday) ?></strong>
        <?php if ($averagePreparationMinutes !== null) : ?>
            · Avg prep: <strong><?= Html::encode((string) $averagePreparationMinutes) ?> min</strong>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <?php foreach ($statuses as $status) : ?>
        <?php $meta = $columnMeta[$status] ?? ['label' => Order::statusLabel($status), 'class' => 'text-bg-light']; ?>
        <div class="d-flex align-items-center gap-2">
            <span class="badge <?= Html::encode($meta['class']) ?>"><?= Html::encode($meta['label']) ?></span>
            <span class="badge text-bg-light border"><?= Html::encode((string) ($counts[$status] ?? 0)) ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <?php foreach ($statuses as $status) : ?>
        <?php $meta = $columnMeta[$status] ?? ['label' => Order::statusLabel($status), 'class' => 'text-bg-light']; ?>
        <div class="col-12 col-md-6 col-xl-3">
            <section class="card h-100 admin-panel">
                <div class="d-flex card-header align-items-center gap-3">
                    <h2 class="mb-0 h6"><?= Html::encode($meta['label']) ?></h2>
                    <span class="badge <?= Html::encode($meta['class']) ?>"><?= Html::encode((string) ($counts[$status] ?? 0)) ?></span>
                </div>
                <div class="card-body bg-body-tertiary" style="min-height: 12rem;">
                    <?php foreach ($columns[$status] ?? [] as $order) : ?>
                        <?= $this->render('_order_card', ['order' => $order]) ?>
                    <?php endforeach; ?>
                    <?php if (($columns[$status] ?? []) === []) : ?>
                        <p class="mb-0 text-body-secondary small">No orders.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    <?php endforeach; ?>
</div>
