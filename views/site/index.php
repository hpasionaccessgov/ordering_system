<?php

use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $featuredDataProvider */
/** @var list<app\models\Product> $specialProducts */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */
/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */

$this->title = 'Cafébara | Cafe ordering';
$specialProducts = $specialProducts ?? [];
$canViewFeaturedProducts = Yii::$app->user->isGuest || Yii::$app->user->can('product.view');
$batchFormId = Yii::$app->user->can('cart.add') ? 'home-batch-cart' : null;
?>
<div class="site-index">
    <section class="p-4 p-lg-5 mb-5 rounded-4 cafebara-hero">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-7">
                <p class="mb-3 cafebara-hero-kicker">
                    <span class="cafebara-kicker-dot" aria-hidden="true"></span>
                    Your next favorite is waiting
                </p>

                <h1 class="mb-3 fw-bold display-4">
                    Good coffee.<br>
                    <span>Faster ordering.</span>
                </h1>

                <p class="mb-4 lead cafebara-hero-copy">
                    Explore featured drinks, see what everyone is ordering, and grab today’s specials in a few taps.
                </p>

                <div class="d-flex flex-wrap gap-2 mb-4 cafebara-hero-chips">
                    <span class="badge rounded-pill"><i class="bi bi-stars me-1" aria-hidden="true"></i>Featured drinks</span>
                    <span class="badge rounded-pill"><i class="bi bi-trophy me-1" aria-hidden="true"></i>Best sellers</span>
                    <span class="badge rounded-pill"><i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>Today’s specials</span>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <?= Html::a('Order Now', ['/site/menu'], ['class' => 'btn btn-cafebara-light btn-lg']) ?>
                    <?= Html::a('Browse Menu', ['/site/menu'], ['class' => 'btn btn-cafebara-outline-light btn-lg']) ?>
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

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <?php if ($canViewFeaturedProducts && $featuredDataProvider->getTotalCount() > 0) : ?>
                <section class="mb-4 cafebara-featured">
                    <div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-end">
                        <div>
                            <p class="mb-2 cafebara-section-kicker">From the counter</p>
                            <h2 class="mb-0 fw-bold h2">Featured drinks and bites</h2>
                        </div>
                        <?= Html::a('Browse menu', ['/site/menu'], ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?php if ($batchFormId !== null) : ?>
                        <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
                    <?php endif; ?>
                    <div class="row g-3">
                        <?php foreach ($featuredDataProvider->getModels() as $product) : ?>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <?= $this->render('../shared/_product_card', [
                                    'product' => $product,
                                    'batchFormId' => $batchFormId,
                                    'favoriteIds' => $favoriteIds ?? [],
                                    'ratingSummaries' => $ratingSummaries ?? [],
                                ]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($canViewFeaturedProducts && $specialProducts !== []) : ?>
                <section class="mb-4 cafebara-specials">
                    <div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-end">
                        <div>
                            <p class="mb-2 cafebara-section-kicker">Limited-time treats</p>
                            <h2 class="mb-0 fw-bold h2">Today’s specials</h2>
                        </div>
                        <span class="badge rounded-pill text-bg-warning">
                            <i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>Ask the counter for details
                        </span>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($specialProducts as $product) : ?>
                            <div class="col-12 col-sm-6 col-xl-3">
                                <?= $this->render('../shared/_product_card', [
                                    'product' => $product,
                                    'batchFormId' => $batchFormId,
                                    'favoriteIds' => $favoriteIds ?? [],
                                    'ratingSummaries' => $ratingSummaries ?? [],
                                ]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <div class="col-12 col-xl-4">
            <?= $this->render('../shared/_best_sellers', ['bestSellers' => $bestSellers ?? []]) ?>

            <section class="card cafebara-feature-card h-100">
                <div class="card-body p-4">
                    <span class="cafebara-feature-icon" aria-hidden="true">
                        <i class="bi bi-phone"></i>
                    </span>
                    <h2 class="h5 fw-bold mt-4">Order from any screen</h2>
                    <p class="text-body-secondary mb-3">
                        Save favorites, check your rewards, and follow your order from phone, tablet, or desktop.
                    </p>
                    <?= Html::a('See the full menu', ['/site/menu'], ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </section>
        </div>
    </div>
</div>
