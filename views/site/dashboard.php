<?php

/** @var yii\web\View $this */

use yii\helpers\Html;
use app\widgets\AdminButton;

$this->title = 'Dashboard';
$user = Yii::$app->user->identity;
$displayName = $user?->full_name ?: ($user?->username ?? 'there');
?>
<div class="cafebara-dashboard">
    <section class="cafebara-dashboard-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <p class="cafebara-dashboard-kicker mb-2">YOUR CAFÉBARA WORKSPACE</p>
                <h2 class="display-6 fw-bold mb-3">
                    Welcome back!, <?= Html::encode($displayName) ?>.
                </h2>
                <p class="lead mb-4">
                    This is the home for your cafe activity and ordering tools.
                </p>
                <?= AdminButton::link('Manage profile', ['/site/profile'], 'cafebara-light') ?>
            </div>
            <div class="col-lg-4 d-none d-lg-flex justify-content-center">
                <?= Html::img(Yii::getAlias('@web/images/Cafebara_logo.png'), [
                    'alt' => '',
                    'class' => 'cafebara-dashboard-logo',
                ]) ?>
            </div>
        </div>
    </section>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-xl-4">
            <section class="card cafebara-dashboard-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-body-secondary mb-2">Order activity</p>
                            <h3 class="h4 fw-bold mb-2">Coming soon</h3>
                        </div>
                        <span class="cafebara-dashboard-icon" aria-hidden="true">
                            <i class="bi bi-bag-check"></i>
                        </span>
                    </div>
                    <p class="text-body-secondary mb-0">
                        Order summaries will appear here when the ordering module is connected.
                    </p>
                </div>
            </section>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <section class="card cafebara-dashboard-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-body-secondary mb-2">Cafe menu</p>
                            <h3 class="h4 fw-bold mb-2">Coming soon</h3>
                        </div>
                        <span class="cafebara-dashboard-icon" aria-hidden="true">
                            <i class="bi bi-cup-hot"></i>
                        </span>
                    </div>
                    <p class="text-body-secondary mb-0">
                        Menu tools can be added here in a future ordering phase.
                    </p>
                </div>
            </section>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <section class="card cafebara-dashboard-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <p class="text-body-secondary mb-2">Account</p>
                            <h3 class="h4 fw-bold mb-2">Ready</h3>
                        </div>
                        <span class="cafebara-dashboard-icon" aria-hidden="true">
                            <i class="bi bi-person-check"></i>
                        </span>
                    </div>
                    <p class="text-body-secondary mb-3">
                        Update your personal details or change your password.
                    </p>
                    <?= AdminButton::link('Account settings', ['/site/profile'], 'link', [
                        'class' => 'fw-semibold',
                    ]) ?>
                </div>
            </section>
        </div>
    </div>

    <section class="card cafebara-dashboard-activity mt-4">
        <div class="card-header bg-transparent py-3">
            <h2 class="h5 fw-bold mb-0">Recent activity</h2>
        </div>
        <div class="card-body">
            <div class="cafebara-empty-state text-center py-4">
                <span class="cafebara-empty-icon mb-3" aria-hidden="true">
                    <i class="bi bi-journal-text"></i>
                </span>
                <h3 class="h6 fw-bold">Nothing to show just yet</h3>
                <p class="text-body-secondary mb-0">
                    Activity will appear here after cafe ordering is connected.
                </p>
            </div>
        </div>
    </section>
</div>
