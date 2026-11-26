<?php

use app\services\Navigation;
use yii\helpers\Html;

/** @var list<app\models\Product> $featuredProducts */
/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */

$featuredProducts = $featuredProducts ?? [];
$bestSellers = $bestSellers ?? [];
$batchFormId = Yii::$app->user->can('cart.add') ? 'dashboard-showcase-cart' : null;
?>
<div class="row g-4">
    <div class="col-12 col-xl-8">
        <?php if ($featuredProducts !== [] && Yii::$app->user->can('product.view')) : ?>
            <section class="card h-100 cafebara-dashboard-card">
                <div class="d-flex flex-nowrap gap-3 card-header justify-content-between align-items-center">
                    <h2 class="mb-0 h5">Featured drinks and bites</h2>
                    <?php if (Yii::$app->user->can('product.index')) : ?>
                        <?= Html::a('Browse Menu', Navigation::url(Navigation::MENU), ['class' => 'btn btn-primary btn-sm']) ?>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($batchFormId !== null) : ?>
                        <?= $this->render('_batch_cart', ['batchFormId' => $batchFormId]) ?>
                    <?php endif; ?>
                    <div class="row g-3">
                        <?php foreach ($featuredProducts as $product) : ?>
                            <div class="col-12 col-sm-6 col-xl-4">
                                <?= $this->render('_product_card', [
                                    'product' => $product,
                                    'batchFormId' => $batchFormId,
                                    'favoriteIds' => $favoriteIds ?? [],
                                    'ratingSummaries' => $ratingSummaries ?? [],
                                ]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>
    <div class="col-12 col-xl-4 d-flex flex-column">
        <?= $this->render('_best_sellers', ['bestSellers' => $bestSellers]) ?>
    </div>
</div>
