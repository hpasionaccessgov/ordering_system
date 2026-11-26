<?php

use app\services\Navigation;
/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About';
// $this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Learn more about this Yii2-powered application.';
$this->params['meta_keywords'] = 'yii, yii2, about, php, framework';
?>
<div class="site-about">
    <div class="container py-4">
        <section class="p-4 p-lg-5 mb-5 cafebara-about-hero">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <p class="mb-2 cafebara-dashboard-kicker">A little about our workspace</p>
                    <h1 class="mb-3 fw-bold display-5">Welcome to Cafébara</h1>
                    <p class="mb-4 lead">
                        Cafébara is a cafe ordering workspace for discovering drinks and bites,
                        placing orders, and keeping track of every order's progress.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <!-- <?= Html::a('Browse the menu', Navigation::url(Navigation::PRODUCT_INDEX), [
                            'class' => 'btn btn-cafebara-light',
                        ]) ?> -->
                        <?= Html::a('Get in touch', Navigation::url(Navigation::CONTACT), [
                            'class' => 'btn btn-cafebara-light',
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
