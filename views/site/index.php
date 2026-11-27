<?php

use app\services\Navigation;
use yii\helpers\Html;

/** @var list<app\models\Product> $featuredProducts */
/** @var list<app\models\Product> $specialProducts */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */
/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */

$this->title = 'Cafébara | Cafe ordering';
$batchFormId = Yii::$app->user->can('cart.add') ? 'home-batch-cart' : null;
?>
<div class="site-index">
    <section class="p-4 p-lg-5 mb-5 rounded-4 cafebara-hero">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-7">
                <h1 class="mb-3 fw-bold display-4">
                    Good coffee, without the wait.
                </h1>

                <p class="mb-4 lead cafebara-hero-copy">
                    Browse featured drinks, see what everyone is ordering, and grab today’s specials.
                </p>

                <div class="d-flex flex-wrap gap-2">
                    <?= Html::a('Order Now', Navigation::url(Navigation::ORDER_CREATE), ['class' => 'btn btn-cafebara-light btn-lg']) ?>
                    <?= Html::a('Browse Menu', Navigation::url(Navigation::MENU), ['class' => 'btn btn-cafebara-outline-light btn-lg']) ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="cafebara-hero-art">
                    <?= Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
                        'alt' => 'Cafébara mascot holding a cup of coffee',
                        'class' => 'cafebara-hero-logo',
                        'width' => 1254,
                        'height' => 1254,
                        'fetchpriority' => 'high',
                    ]) ?>
                    <div class="cafebara-hero-note">
                        <span class="cafebara-hero-note-icon" aria-hidden="true">
                            <i class="bi bi-cup-hot-fill"></i>
                        </span>
                        <span>
                            <strong>Your cafe, your pace</strong>
                            <small>What's good dawg</small>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <?= $this->render('../shared/_product_highlights', [
                'featuredProducts' => $featuredProducts ?? [],
                'specialProducts' => $specialProducts ?? [],
                'batchFormId' => $batchFormId,
                'favoriteIds' => $favoriteIds ?? [],
                'ratingSummaries' => $ratingSummaries ?? [],
            ]) ?>
        </div>

        <div class="col-12 col-xl-4 d-flex flex-column">
            <?= $this->render('../shared/_best_sellers', ['bestSellers' => $bestSellers ?? []]) ?>

            <section class="card cafebara-feature-card">
                <div class="p-4 card-body">
                    <span class="cafebara-feature-icon" aria-hidden="true">
                        <i class="bi bi-phone"></i>
                    </span>
                    <span class="cafebara-feature-icon" aria-hidden="true">
                        <i class="bi bi-laptop"></i>
                    </span>
                    <span class="cafebara-feature-icon" aria-hidden="true">
                        <i class="bi bi-tablet-landscape"></i>
                    </span>
                    <h2 class="mt-4 fw-bold h5">Order from any screen</h2>
                    <p class="mb-3 text-body-secondary">
                        Save favorites and check your rewards.
                    </p>

                    <p class="mb-3 text-body-secondary">
                        Download the application for additional points!
                    </p>
                    <?= Html::a('Download App', Navigation::url(Navigation::MENU), ['class' => 'btn btn-outline-primary']) ?> <!-- Temporary as public menu nav but will change to actual app download link -->
                </div>
            </section>
        </div>
    </div>
</div>