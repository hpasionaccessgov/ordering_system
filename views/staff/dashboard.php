<?php

/** @var yii\web\View $this */
/** @var int $activeProductCount */
/** @var int $pendingOrderCount */
/** @var int $processingOrderCount */
/** @var array<string, int> $orderStatusCounts */

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
                <?= Html::a('Process orders', ['/order/index'], ['class' => 'btn btn-cafebara-light']) ?>
                <?= Html::a('Manage products', ['/product/index'], ['class' => 'btn btn-cafebara-outline-light']) ?>
            </div>
        </div>
        <div class="col-auto d-none d-sm-block">
            <?= Html::img(Yii::getAlias('@web/images/Cafebara_logo.png'), [
                'class' => 'dashboard-welcome-logo',
                'alt' => '',
                'aria-hidden' => 'true',
            ]) ?>
        </div>
    </div>
</section>

<div class="row g-3 mb-4">
    <?php foreach (
    [
        ['Active products', $activeProductCount, 'bi-cup-hot'],
        ['Pending orders', $pendingOrderCount, 'bi-hourglass-split'],
        ['Processing orders', $processingOrderCount, 'bi-arrow-repeat'],
    ] as [$label, $value, $icon]
) : ?>
        <div class="col-12 col-md-4">
            <section class="small-box cafebara-stat-card h-100">
                <div class="inner">
                    <p><?= Html::encode($label) ?></p>
                    <h3><?= Html::encode((string) $value) ?></h3>
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

<!-- <div class="row g-4">
    <div class="col-12 col-xl-6">
        <section class="card dashboard-action-card h-100">
            <div class="card-body">
                <span class="cafebara-dashboard-icon mb-3"><i class="bi bi-cup-hot" aria-hidden="true"></i></span>
                <h2 class="h5">Product management</h2>
                <p class="text-body-secondary">Maintain active products, pricing, categories, and featured items.</p>
                <?= Html::a('Manage products', ['/product/index'], ['class' => 'btn btn-primary']) ?>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card dashboard-action-card h-100">
            <div class="card-body">
                <span class="cafebara-dashboard-icon mb-3"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                <h2 class="h5">Order processing</h2>
                <p class="text-body-secondary">Review customer orders and move them through the service workflow.</p>
                <?= Html::a('Process orders', ['/order/index'], ['class' => 'btn btn-primary']) ?>
            </div>
        </section>
    </div>
</div> -->
