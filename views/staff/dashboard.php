<?php

/** @var yii\web\View $this */
/** @var int $activeProductCount */
/** @var int $pendingOrderCount */
/** @var int $processingOrderCount */
/** @var int $inventoryCount */
/** @var int $lowStockCount */
/** @var int $outOfStockCount */
/** @var \app\models\Inventory[] $lowStockItems */
/** @var array<string, int> $orderStatusCounts */
/** @var array{revenue: float, orders: int, completed: int, cancelled: int, aov: float} $todaySales */

use app\models\User;
use yii\helpers\Html;

$this->title = 'Staff dashboard';
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
?>

<section class="card dashboard-welcome-card dashboard-welcome-staff border-0 mb-4">
    <div class="card-body row align-items-center g-4">
        <div class="col">
            <span class="dashboard-welcome-role mb-3">
                <i class="bi bi-cup-hot" aria-hidden="true"></i> Staff
            </span>
            <h1 class="h3 mt-2 mb-2">Welcome, <?= Html::encode($displayName) ?></h1>
            <p class="mb-3">Keep the menu current and move customer orders through service.</p>
            <div class="d-flex flex-wrap gap-2">
                <?php if (Yii::$app->user->can('order.index')) : ?>
                    <?= Html::a('Process orders', ['/order/index'], ['class' => 'btn btn-cafebara-light']) ?>
                <?php elseif (Yii::$app->user->can('order.create')) : ?>
                    <?= Html::a('Create order', ['/order/create'], ['class' => 'btn btn-cafebara-light']) ?>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('product.index')) : ?>
                    <?= Html::a('Manage products', ['/product/index'], ['class' => 'btn btn-cafebara-outline-light']) ?>
                <?php elseif (Yii::$app->user->can('product.create')) : ?>
                    <?= Html::a('Create product', ['/product/create'], ['class' => 'btn btn-cafebara-outline-light']) ?>
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
            ["Today's revenue", Yii::$app->formatter->asCurrency($todaySales['revenue']), 'bi-cash-coin'],
            ["Today's orders", $todaySales['orders'], 'bi-receipt'],
            ['Average order value', Yii::$app->formatter->asCurrency($todaySales['aov']), 'bi-graph-up'],
            ['Current low stock', $lowStockCount, 'bi-exclamation-triangle'],
            ['Active products', $activeProductCount, 'bi-cup-hot'],
            ['Pending orders', $pendingOrderCount, 'bi-hourglass-split'],
            ['Processing orders', $processingOrderCount, 'bi-arrow-repeat'],
            ['Inventory items', $inventoryCount, 'bi-box-seam'],
            ['Out of stock', $outOfStockCount, 'bi-x-circle'],
        ] as [$label, $value, $icon]
    ) : ?>
        <div class="col-12 col-md-4">
            <section class="small-box cafebara-stat-card h-100">
                <div class="inner">
                    <p><?= Html::encode($label) ?></p>
                    <h3 class="cafebara-stat-value"><?= Html::encode((string) $value) ?></h3>
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