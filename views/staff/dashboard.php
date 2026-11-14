<?php

/** @var yii\web\View $this */

use app\models\User;
use yii\helpers\Html;

$this->title = 'Staff dashboard';
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
?>

<section class="cafebara-dashboard-hero mb-4">
    <div>
        <p class="text-uppercase small fw-semibold mb-2">Cafébara staff</p>
        <h1 class="h3 mb-2">Welcome, <?= Html::encode($displayName) ?></h1>
        <p class="mb-0">Your workspace for cafe operations.</p>
    </div>
</section>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <span class="cafebara-dashboard-icon mb-3"><i class="bi bi-cup-hot" aria-hidden="true"></i></span>
                <h2 class="h5">Product management</h2>
                <p class="text-body-secondary mb-0">
                    Product management will be available after the product model, database table, and controller
                    are added.
                </p>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <span class="cafebara-dashboard-icon mb-3"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                <h2 class="h5">Order processing</h2>
                <p class="text-body-secondary mb-0">
                    Order processing will be available after the order workflow and ownership rules are implemented.
                </p>
            </div>
        </section>
    </div>
</div>