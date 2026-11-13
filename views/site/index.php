<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Cafébara | Cafe ordering';
?>
<div class="site-index">
    <section class="cafebara-hero rounded-4 p-4 p-lg-5 mb-5">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7">
                <p class="cafebara-hero-kicker mb-3">
                    <!-- <span class="cafebara-kicker-dot" aria-hidden="true"></span> -->
                    A relaxed in every coffee break
                </p>

                <h1 class="display-4 fw-bold mb-3">
                    Good coffee.<br>
                    <span>Easy ordering.</span>
                </h1>

                <p class="lead cafebara-hero-copy mb-4">
                    Welcome to Cafébara, your place to manage an account and follow the cafe tools as they grow.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <?php if (Yii::$app->user->isGuest): ?>
                        <?= Html::a('Create your account', ['/site/register'], [
                            'class' => 'btn btn-cafebara-light btn-lg',
                        ]) ?>
                        <?= Html::a('Sign in', ['/site/login'], [
                            'class' => 'btn btn-cafebara-outline-light btn-lg',
                        ]) ?>
                    <?php else: ?>
                        <?= Html::a('Open your dashboard', ['/site/dashboard'], [
                            'class' => 'btn btn-cafebara-light btn-lg',
                        ]) ?>
                    <?php endif ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="cafebara-hero-art">
                    <?= Html::img(Yii::getAlias('@web/images/Cafebara_logo.png'), [
                        'alt' => 'Cafébara mascot holding a cup of coffee',
                        'class' => 'cafebara-hero-logo',
                    ]) ?>
                    <div class="cafebara-hero-note">
                        <span class="cafebara-hero-note-icon" aria-hidden="true">
                            <i class="bi bi-cup-hot"></i>
                        </span>
                        <span>
                            <strong>Your cafe, your pace</strong>
                            <small>A warm welcome starts here</small>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cafebara-intro pb-4">
        <div class="row align-items-end g-3 mb-4">
            <div class="col-lg-7">
                <p class="cafebara-section-kicker mb-2">WELCOME TO CAFÉBARA!</p>
                <h2 class="h2 fw-bold mb-0">Your cafe, all in one place.</h2>
            </div>
            <div class="col-lg-5">
                <p class="text-body-secondary mb-0">
                    Start with your account today. Cafébara's ordering workspace is designed to grow with the cafe.
                </p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <article class="card cafebara-feature-card h-100">
                    <div class="card-body p-4">
                        <span class="cafebara-feature-icon" aria-hidden="true">
                            <i class="bi bi-person-check"></i>
                        </span>
                        <h3 class="h5 fw-bold mt-4">Your account</h3>
                        <p class="text-body-secondary mb-0">
                            Create an account and keep your profile details close at hand.
                        </p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-4">
                <article class="card cafebara-feature-card h-100">
                    <div class="card-body p-4">
                        <span class="cafebara-feature-icon" aria-hidden="true">
                            <i class="bi bi-cup-hot"></i>
                        </span>
                        <h3 class="h5 fw-bold mt-4">Cafe workspace</h3>
                        <p class="text-body-secondary mb-0">
                            A clear dashboard is ready for the cafe's ordering tools and activity.
                        </p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-4">
                <article class="card cafebara-feature-card h-100">
                    <div class="card-body p-4">
                        <span class="cafebara-feature-icon" aria-hidden="true">
                            <i class="bi bi-phone"></i>
                        </span>
                        <h3 class="h5 fw-bold mt-4">Made for every screen</h3>
                        <p class="text-body-secondary mb-0">
                            Use Cafébara comfortably on a phone, tablet, or desktop.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
