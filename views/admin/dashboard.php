<?php

/** @var yii\web\View $this */

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

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <span class="cafebara-dashboard-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                    <div>
                        <h2 class="h5">User roles</h2>
                        <p class="text-body-secondary">Assign each account its Admin, Staff, or Customer role.</p>
                        <?= AdminButton::link('Manage user roles', ['/admin-user/index'], 'primary') ?>
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
                        <p class="text-body-secondary mb-0">
                            These workflows are not available yet because the product and order modules are not
                            implemented in this codebase.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>