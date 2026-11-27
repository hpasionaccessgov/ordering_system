<?php

use app\services\Navigation;
use yii\helpers\Html;

/** @var list<app\models\Product> $specialProducts */
/** @var list<app\models\Product> $featuredProducts */
/** @var string|null $batchFormId */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */

$specialProducts = $specialProducts ?? [];
$featuredProducts = $featuredProducts ?? [];
$batchFormId = $batchFormId ?? (Yii::$app->user->can('cart.add') ? 'highlights-batch-cart' : null);
$canViewProducts = Yii::$app->user->isGuest || Yii::$app->user->can('product.view');
$specialIds = array_map(static fn ($product): int => (int) $product->id, $specialProducts);
?>
<?php if ($canViewProducts && ($specialProducts !== [] || $featuredProducts !== [])) : ?>
    <?php if ($batchFormId !== null) : ?>
        <?= $this->render('_batch_cart', ['batchFormId' => $batchFormId]) ?>
    <?php endif; ?>
    <section class="cafebara-product-highlights">
        <div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-end">
            <div>
                <p class="cafebara-section-kicker mb-1">Picked for you</p>
                <h2 class="mb-0 fw-bold h4">Today’s specials</h2>
                <p class="mb-0 text-body-secondary small">Limited-time drinks and bites worth trying today.</p>
            </div>
            <?= Html::a('Browse menu', Navigation::url(Navigation::MENU), ['class' => 'btn btn-outline-primary btn-sm']) ?>
        </div>
        <?php if ($specialProducts !== []) : ?>
            <div class="row g-3 mb-4">
                <?php foreach ($specialProducts as $product) : ?>
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
        <?php endif; ?>
        <div class="border-top border-primary-subtle pt-4">
            <h2 class="mb-1 fw-bold h4">Featured drinks & bites</h2>
            <p class="mb-3 text-body-secondary small">Customer favorites and new picks from the menu.</p>
            <div class="row g-3">
                <?php foreach ($featuredProducts as $product) : ?>
                    <div class="col-12 col-sm-6 col-xl-4">
                        <?= $this->render('_product_card', [
                            'product' => $product,
                            'batchFormId' => in_array((int) $product->id, $specialIds, true) ? null : $batchFormId,
                            'favoriteIds' => $favoriteIds ?? [],
                            'ratingSummaries' => $ratingSummaries ?? [],
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
