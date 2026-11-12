<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Dashboard';
?>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-body-secondary mb-1">Orders today</p>
                <div class="fs-2 fw-semibold">0</div>
                <i class="bi bi-bag-check fs-4 text-primary" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-body-secondary mb-1">Pending orders</p>
                <div class="fs-2 fw-semibold">0</div>
                <i class="bi bi-hourglass-split fs-4 text-warning" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-body-secondary mb-1">Registered users</p>
                <div class="fs-2 fw-semibold">0</div>
                <i class="bi bi-people fs-4 text-success" aria-hidden="true"></i>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h2 class="card-title mb-0">Yoh, Welcome</h2>
    </div>
    <div class="card-body">
        <p>Your Cafébara account is ready. This dashboard will show admin cafe activity here.</p>
        <?= Html::a('View your profile', ['/site/profile'], ['class' => 'btn btn-primary']) ?>
    </div>
</div>