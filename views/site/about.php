<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About';
// $this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Learn more about this Yii2-powered application.';
$this->params['meta_keywords'] = 'yii, yii2, about, php, framework';
?>
<div class="site-about">
    <div class="container py-4">
        <section class="cafebara-about-hero p-4 p-lg-5 mb-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <p class="cafebara-dashboard-kicker mb-2">A little about our workspace</p>
                    <h1 class="display-5 fw-bold mb-3">Welcome to Cafébara</h1>
                    <p class="lead mb-4">
                        Cafébara is a cafe ordering workspace for discovering drinks and bites,
                        placing orders, and keeping track of every order's progress.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <?= Html::a('Browse the menu', ['/product/index'], [
                            'class' => 'btn btn-cafebara-light',
                        ]) ?>
                        <?= Html::a('Get in touch', ['/site/contact'], [
                            'class' => 'btn btn-cafebara-outline-light',
                        ]) ?>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <?= Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
                        'class' => 'cafebara-about-logo',
                        'alt' => 'Cafébara capybara mascot',
                    ]) ?>
                </div>
            </div>
        </section>

        <section aria-labelledby="about-experience-title">
            <p class="cafebara-section-kicker mb-2">Made for everyday cafe moments</p>
            <h2 class="h2 fw-bold mb-4" id="about-experience-title">
                A simple experience from menu to order
            </h2>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <article class="cafebara-public-info-card">
                        <span class="cafebara-feature-icon" aria-hidden="true">
                            <i class="bi bi-cup-hot"></i>
                        </span>
                        <h3 class="h5">Explore the menu</h3>
                        <p class="text-body-secondary mb-0">
                            Browse available drinks and pastries with clear descriptions and prices.
                        </p>
                    </article>
                </div>
                <div class="col-12 col-md-4">
                    <article class="cafebara-public-info-card">
                        <span class="cafebara-feature-icon" aria-hidden="true">
                            <i class="bi bi-bag-check"></i>
                        </span>
                        <h3 class="h5">Place an order</h3>
                        <p class="text-body-secondary mb-0">
                            Sign in to create an order and review your own order history.
                        </p>
                    </article>
                </div>
                <div class="col-12 col-md-4">
                    <article class="cafebara-public-info-card">
                        <span class="cafebara-feature-icon" aria-hidden="true">
                            <i class="bi bi-arrow-repeat"></i>
                        </span>
                        <h3 class="h5">Follow its progress</h3>
                        <p class="text-body-secondary mb-0">
                            See status updates as staff confirm and prepare orders.
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </div>
</div>
