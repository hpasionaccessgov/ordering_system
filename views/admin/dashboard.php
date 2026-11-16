<?php

/** @var yii\web\View $this */
/** @var int $userCount */
/** @var int $activeUserCount */
/** @var int $productCount */
/** @var int $pendingOrderCount */
/** @var array<string, int> $orderStatusCounts */

use app\models\User;
use yii\helpers\Html;

$this->title = 'Admin dashboard';
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
?>

<section class="card dashboard-welcome-card dashboard-welcome-admin border-0 mb-4">
    <div class="card-body row align-items-center g-4">
        <div class="col">
            <span class="dashboard-welcome-role mb-3">
                <i class="bi bi-shield-check" aria-hidden="true"></i> Administration
            </span>
            <h1 class="h3 mt-2 mb-2">Welcome, <?= Html::encode($displayName) ?></h1>
            <p class="mb-3">Manage access, keep the catalog healthy, and monitor cafe operations.</p>
            <div class="d-flex flex-wrap gap-2">
                <?= Html::a('Manage people', ['/admin-user/list'], ['class' => 'btn btn-cafebara-light']) ?>
                <?= Html::a('Review roles', ['/admin-role/index'], ['class' => 'btn btn-cafebara-outline-light']) ?>
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
            ['People with access', $userCount, 'bi-people', 'Total accounts'],
            ['Active accounts', $activeUserCount, 'bi-person-check', 'Allowed to sign in'],
            ['Active products', $productCount, 'bi-cup-hot', 'Visible in catalog'],
            ['Pending orders', $pendingOrderCount, 'bi-receipt', 'Need attention'],
        ] as [$label, $value, $icon, $caption]
) : ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="small-box cafebara-stat-card h-100">
                <div class="inner">
                    <p><?= Html::encode($label) ?></p>
                    <h3><?= Html::encode((string) $value) ?></h3>
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

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <section class="card dashboard-action-card h-100">
            <div class="card-body">
                <span class="cafebara-dashboard-icon mb-3"><i class="bi bi-people" aria-hidden="true"></i></span>
                <h2 class="h5">People and access</h2>
                <p class="text-body-secondary">Manage account status, roles, and permissions.</p>
                <?= Html::a('Open user list', ['/admin-user/list'], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Open roles', ['/admin-role/index'], ['class' => 'btn btn-outline-primary']) ?>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card dashboard-action-card h-100">
            <div class="card-body">
                <span class="cafebara-dashboard-icon mb-3"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <h2 class="h5">Products and orders</h2>
                <p class="text-body-secondary">Review the catalog and order activity.</p>
                <?= Html::a('Products', ['/product/index'], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Orders', ['/order/index'], ['class' => 'btn btn-outline-primary']) ?>
            </div>
        </section>
    </div>
</div>
