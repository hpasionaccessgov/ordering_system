<?php

/** @var yii\web\View $this */

use app\models\User;
use yii\helpers\Html;

$this->title = 'Customer dashboard';
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
?>

<section class="cafebara-dashboard-hero mb-4">
    <div>
        <p class="text-uppercase small fw-semibold mb-2">Your Cafébara account</p>
        <h1 class="h3 mb-2">Welcome, <?= Html::encode($displayName) ?></h1>
        <p class="mb-0">Discover the menu and keep track of your cafe orders.</p>
    </div>
</section>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <span class="cafebara-dashboard-icon mb-3"><i class="bi bi-cup-hot" aria-hidden="true"></i></span>
                <h2 class="h5">Browse the menu</h2>
                <p class="text-body-secondary mb-0">
                    The product catalog is not available yet. It will appear here after the product module is built.
                </p>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card h-100">
            <div class="card-body">
                <span class="cafebara-dashboard-icon mb-3"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                <h2 class="h5">Your orders</h2>
                <p class="text-body-secondary mb-0">
                    Your order history and order placement will appear here once ordering is implemented.
                </p>
            </div>
        </section>
    </div>
</div>