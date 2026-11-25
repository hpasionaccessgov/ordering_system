<?php

/** @var yii\web\View $this */

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $featuredDataProvider */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */
/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */

$this->title = 'Cafébara | Cafe ordering';
$canViewFeaturedProducts = Yii::$app->user->isGuest || Yii::$app->user->can('product.view');
$batchFormId = Yii::$app->user->can('cart.add') ? 'home-batch-cart' : null;
?>
<div class="site-index">
    <section class="p-4 p-lg-5 mb-5 rounded-4 cafebara-hero">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-7">
                <p class="mb-3 cafebara-hero-kicker">
                    <!-- <span class="cafebara-kicker-dot" aria-hidden="true"></span> -->
                    A relaxed in every coffee break
                </p>

                <h1 class="mb-3 fw-bold display-4">
                    Good coffee.<br>
                    <span>Easy ordering.</span>
                </h1>

                <p class="mb-4 lead cafebara-hero-copy">
                    Welcome to Cafébara! follow the cafe tools as they grow.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <?php if (Yii::$app->user->isGuest) : ?>
                        <?= Html::a('Create your account', ['/site/register'], [
                            'class' => 'btn btn-cafebara-light btn-lg',
                        ]) ?>
                        <?= Html::a('Sign in', ['/site/login'], [
                            'class' => 'btn btn-cafebara-outline-light btn-lg',
                        ]) ?>
                    <?php else : ?>
                        <?= Html::a('Open your dashboard', ['/site/dashboard'], [
                            'class' => 'btn btn-cafebara-light btn-lg',
                        ]) ?>
                    <?php endif ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="cafebara-hero-art">
                    <?= Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
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

    <?php if ($canViewFeaturedProducts && $featuredDataProvider->getTotalCount() > 0) : ?>
        <section class="mb-5 cafebara-featured">
            <div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-end">
                <div>
                    <p class="mb-2 cafebara-section-kicker">From the counter</p>
                    <h2 class="mb-0 fw-bold h2">Featured drinks and bites</h2>
                </div>
                <?= Html::a(
                    'Browse menu',
                    ['/site/menu'],
                    ['class' => 'btn btn-primary'],
                ) ?>
            </div>
            <?php if ($batchFormId !== null) : ?>
                <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
            <?php endif; ?>
            <div class="row g-4">
                <?php foreach ($featuredDataProvider->getModels() as $product) : ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <?= $this->render('../shared/_product_card', [
                            'product' => $product,
                            'batchFormId' => $batchFormId,
                            'favoriteIds' => $favoriteIds ?? [],
                            'ratingSummaries' => $ratingSummaries ?? [],
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex mt-4 justify-content-center">
                <?= LinkPager::widget([
                    'pagination' => $featuredDataProvider->getPagination(),
                    'prevPageLabel' => 'Previous',
                    'nextPageLabel' => 'Next',
                ]) ?>
            </div>
        </section>
    <?php endif; ?>

    <?= $this->render('../shared/_best_sellers', ['bestSellers' => $bestSellers ?? []]) ?>

    <section class="pb-4 cafebara-intro">
        <div class="row g-3 mb-4 align-items-end">
            <div class="col-lg-7">
                <p class="mb-2 cafebara-section-kicker">WELCOME TO CAFÉBARA!</p>
                <h2 class="mb-0 fw-bold h2">Your cafe, all in one place.</h2>
            </div>
            <!-- <div class="col-lg-5">
                <p class="mb-0 text-body-secondary">
                    Start with your account today. Cafébara's ordering workspace is designed to grow with the cafe.
                </p>
            </div> -->
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
                            A dashboard ready for the cafe's ordering tools and activity.
                        </p>
                    </div>
                </article>
            </div>

            <div class="col-12 col-md-4">
                <article class="card h-100 cafebara-feature-card">
                    <div class="p-4 card-body">
                        <span class="cafebara-feature-icon" aria-hidden="true">
                            <i class="bi bi-phone"></i>
                        </span>
                        <h3 class="mt-4 fw-bold h5">Made for each screen</h3>
                        <p class="mb-0 text-body-secondary">
                            Access Cafébara on any device.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>
