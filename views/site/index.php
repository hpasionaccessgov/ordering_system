<?php

use app\services\Navigation;
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
// One batch input per product; duplicate names let a later zero overwrite the edited value.
$specialIds = array_map(static fn ($product): int => (int) $product->id, $specialProducts);
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

            <?php if ($canViewFeaturedProducts && $featuredDataProvider->getTotalCount() > 0) : ?>
                <section class="mb-4 cafebara-featured">
                    <div class="d-flex flex-wrap gap-3 mb-4 justify-content-end align-items-end">
                        <?= Html::a('Browse Menu', Navigation::url(Navigation::MENU), ['class' => 'btn btn-primary',]) ?>
                    </div>
                    <?php if ($batchFormId !== null) : ?>
                        <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
                    <?php endif; ?>
                    <?php if ($canViewFeaturedProducts && $specialProducts !== []) : ?>
                        <section class="mb-4 cafebara-specials">
                            <div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-end">
                                <div>
                                    <h2 class="mb-0 fw-bold h2">Today’s specials</h2>
                                </div>
                            </div>
                            <div class="row g-3">
                                <?php foreach ($specialProducts as $product) : ?>
                                    <div class="col-12 col-sm-6 col-xl-4">
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
                    <div>
                        <h2 class="mb-4 fw-bold h2">Featured drinks & bites</h2>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($featuredDataProvider->getModels() as $product) : ?>
                            <div class="col-12 col-sm-6 col-xl-4">
                                <?= $this->render('../shared/_product_card', [
                                    'product' => $product,
                                    'batchFormId' => in_array((int) $product->id, $specialIds, true) ? null : $batchFormId,
                                    'favoriteIds' => $favoriteIds ?? [],
                                    'ratingSummaries' => $ratingSummaries ?? [],
                                ]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

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
                    <?= Html::a('See the full menu', Navigation::url(Navigation::MENU), ['class' => 'btn btn-outline-primary']) ?>
                </div>
            </section>
        </div>
    </div>
</div>