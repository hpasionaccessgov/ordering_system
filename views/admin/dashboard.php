<?php

/** @var yii\web\View $this */
/** @var int $userCount */
/** @var int $activeUserCount */
/** @var int $productCount */
/** @var int $pendingOrderCount */

use app\models\User;
use app\widgets\AdminButton;
use yii\helpers\Html;

$this->title = 'Admin dashboard';
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
?>

<section class="cafebara-dashboard-hero mb-4">
    <div>
        <p class="text-uppercase small fw-semibold mb-2">Administration</p>
        <h1 class="h3 mb-2">Welcome, <?= Html::encode($displayName) ?></h1>
        <p class="mb-0">Manage access to Cafébara and monitor the system workspace.</p>
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
): ?>
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

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <span class="cafebara-dashboard-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="h5">People and access</h2>
                        <p class="text-body-secondary">Manage account status, roles, and permissions.</p>
                        <?= AdminButton::link('Open user list', ['/admin-user/list'], 'primary') ?>
                        <?= AdminButton::link('Open roles', ['/admin-role/index'], 'outline-primary') ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <span class="cafebara-dashboard-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="h5">Products and orders</h2>
                        <p class="text-body-secondary">Review the catalog and order activity.</p>
                        <?= AdminButton::link('Products', ['/product/index'], 'primary') ?>
                        <?= AdminButton::link('Orders', ['/order/index'], 'outline-primary') ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
