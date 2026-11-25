<?php

/** @var yii\web\View $this */
/** @var int $pendingOrderCount */
/** @var int $processingOrderCount */
/** @var int $readyForPickupCount */
/** @var int $completedTodayCount */
/** @var float|null $averagePreparationMinutes */
/** @var int $lowStockCount */
/** @var \app\models\Inventory[] $lowStockItems */
/** @var array<string, int> $orderStatusCounts */

use app\models\User;
use yii\helpers\Html;

$this->title = 'Staff dashboard';
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
$avgPrep = $averagePreparationMinutes !== null ? $averagePreparationMinutes . ' min avg prep' : 'No prep data today';
?>

<section class="mb-4 card border-0 dashboard-welcome-card dashboard-welcome-staff">
    <div class="row g-4 card-body align-items-center">
        <div class="col">
            <span class="mb-3 dashboard-welcome-role">
                <i class="bi bi-cup-hot" aria-hidden="true"></i> Staff
            </span>
            <h1 class="mt-2 mb-2 h3">Welcome, <?= Html::encode($displayName) ?></h1>
            <p class="mb-3">Keep the menu current and move customer orders through service.</p>
            <div class="d-flex flex-wrap gap-2">
                <?php if (Yii::$app->user->can('order.board')) : ?>
                    <?= Html::a('Kitchen board', ['/sales/kitchen-board'], ['class' => 'btn btn-cafebara-light']) ?>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order.index')) : ?>
                    <?= Html::a('Process orders', ['/sales/orders'], ['class' => 'btn btn-cafebara-outline-light']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-auto d-none d-sm-block">
            <?= Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
                'class' => 'dashboard-welcome-logo',
                'alt' => '',
                'aria-hidden' => 'true',
            ]) ?>
        </div>
    </div>
</section>

<?= $this->render('../shared/_inventory_alert', ['lowStockItems' => $lowStockItems]) ?>

<div class="row g-3 mb-4">
    <?php foreach (
        [
            ['Pending orders', $pendingOrderCount, 'bi-hourglass-split', 'Needs confirmation'],
            ['Processing orders', $processingOrderCount, 'bi-fire', 'In kitchen'],
            ['Ready for pickup', $readyForPickupCount, 'bi-bag-check', 'Awaiting customer'],
            ['Completed today', $completedTodayCount, 'bi-check2-circle', $avgPrep],
        ] as [$label, $value, $icon, $caption]
    ) : ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="h-100 small-box cafebara-stat-card">
                <div class="inner">
                    <p><?= Html::encode($label) ?></p>
                    <h3 class="cafebara-stat-value"><?= Html::encode((string) $value) ?></h3>
                    <span><?= Html::encode($caption) ?></span>
                </div>
                <i class="small-box-icon bi <?= Html::encode($icon) ?>" aria-hidden="true"></i>
            </section>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <?= $this->render('../shared/_order_status_chart', [
            'orderStatusCounts' => $orderStatusCounts,
        ]) ?>
    </div>
</div>
