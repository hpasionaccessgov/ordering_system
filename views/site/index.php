<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Cafébara';
?>

<section class="p-4 p-md-5 mb-4 rounded-4 bg-primary text-white">
    <div class="col-lg-8">
        <h1 class="display-5 fw-bold">Welcome to Cafébara Broskie</h1>
        <p class="lead">
            Create an account or sign in to access your ordering-system account.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <?php if (Yii::$app->user->isGuest): ?>
                <?= Html::a('Create account', ['/site/register'], [
                    'class' => 'btn btn-light btn-lg',
                ]) ?>

                <?= Html::a('Sign in', ['/site/login'], [
                    'class' => 'btn btn-outline-light btn-lg',
                ]) ?>
            <?php else: ?>
                <?= Html::a('Open dashboard', ['/site/dashboard'], [
                    'class' => 'btn btn-light btn-lg',
                ]) ?>
            <?php endif ?>
        </div>
    </div>
</section>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Account access</h2>
                <p class="mb-0">Register or sign in to access your account.</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Ordering system</h2>
                <p class="mb-0">Use the application for handling orders.</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Profile management</h2>
                <p class="mb-0">Manage your profile and account settings.</p>
            </div>
        </div>
    </div>

    
</div>