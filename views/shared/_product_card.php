<?php

use app\services\Navigation;
use app\models\Product;
use yii\helpers\Html;

/** @var Product $product */
/** @var string|null $batchFormId id of a shared batch cart form, or null for per-card add */
/** @var array<int, int> $favoriteIds product ID => favorite ID */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */

$batchFormId = $batchFormId ?? null;
$favoriteIds = $favoriteIds ?? [];
$ratingSummaries = $ratingSummaries ?? [];
$canViewProduct = Yii::$app->user->isGuest || Yii::$app->user->can('product.view');
$canUpdateProducts = $canViewProduct && Yii::$app->user->can('product.update');
$canArchiveProducts = Yii::$app->user->can('product.archive');
$canDeleteProducts = Yii::$app->user->can('product.delete');
$canViewAudit = $canUpdateProducts || $canArchiveProducts || $canDeleteProducts;
$canFavorite = !Yii::$app->user->isGuest && Yii::$app->user->can('favorite.index');
$isFavorite = isset($favoriteIds[(int) $product->id]);
$stockStatus = $product->stockStatus();
$specialProducts = $product->is_special ? [$product] : [];
?>

<article class="card h-100 position-relative product-card product-catalog-card">
    <div class="product-card-media">
        <?php if ($product->getImageUrl() !== null) : ?>
            <?= Html::img($product->getImageUrl(), [
                'class' => 'product-card-image',
                'alt' => $product->name,
                'width' => 640,
                'height' => 480,
                'loading' => 'lazy',
            ]) ?>
            <?php if ($canViewProduct) : ?>
                <div class="product-card-overlay" aria-hidden="true">
                    <i class="bi bi-zoom-in product-card-overlay-icon"></i>
                    <span class="product-card-overlay-label">View</span>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="product-card-placeholder" aria-hidden="true">
                <i class="bi bi-cup-hot"></i>
            </div>
        <?php endif; ?>
        <?php if ($product->is_special) : ?>
            <span class="badge rounded-pill product-special-badge">
                <i class="me-1 bi bi-lightning-charge-fill" aria-hidden="true"></i>Today’s special
            </span>
        <?php endif; ?>
        <?php if ($canFavorite) : ?>
            <?php
            $favoriteLabel = $isFavorite ? 'Remove from favorites' : 'Add to favorites';
            ?>
            <?= Html::beginForm(
                [$isFavorite ? '/favorite/remove' : '/favorite/add', 'id' => $product->id],
                'POST',
                ['class' => 'product-card-favorite js-favorite'],
            ) ?>
            <?= Html::submitButton('<i class="bi ' . ($isFavorite ? 'bi-heart-fill' : 'bi-heart') . '" aria-hidden="true"></i>', [
                'class' => 'btn btn-sm btn-outline-danger rounded-circle shadow-sm',
                'encode' => false,
                'aria-label' => $favoriteLabel,
                'aria-pressed' => $isFavorite ? 'true' : 'false',
                'data-favorite-button' => '',
                'title' => $favoriteLabel,
            ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>

    <div class="d-flex flex-column card-body">
        <div class="d-flex flex-wrap mb-2 gap-2 align-items-center">
            <p class="mb-0 product-category">
                <?= Html::encode($product->categoryRecord?->name ?? 'Cafébara menu') ?>
            </p>
            <span class="badge rounded-pill <?= Html::encode($product->stockBadgeClass()) ?> product-stock-badge">
                <?= Html::encode($product->stockLabel()) ?>
            </span>
        </div>
        <?= $this->render('_rating_stars', [
            'summary' => $ratingSummaries[(int) $product->id] ?? null,
        ]) ?>
        <h3 class="mb-2 h5">
            <?php if ($canViewProduct) : ?>
                <?= Html::a(Html::encode($product->name), Navigation::url(Navigation::PRODUCT_VIEW, ['id' => $product->id]), [
                    'class' => 'stretched-link link-body-emphasis text-decoration-none',
                ]) ?>
            <?php else : ?>
                <?= Html::encode($product->name) ?>
            <?php endif; ?>
        </h3>
        <p class="mb-3 text-body-secondary small">
            <?= Html::encode($product->description ?? '') ?>
        </p>
        <div class="d-flex mt-auto gap-2 justify-content-between align-items-center">
            <strong class="product-card-price"><?= Yii::$app->formatter->asCurrency($product->price) ?></strong>
        </div>
        <?php
        $canAddToCart = Yii::$app->user->isGuest || Yii::$app->user->can('cart.add');
        ?>
        <?php if ($canAddToCart && $stockStatus !== 'out') : ?>
            <?php if ($batchFormId !== null) : ?>
                <div class="d-flex gap-2 mt-3 align-items-center position-relative z-2">
                    <label class="mb-0 form-label small" for="qty-<?= $product->id ?>">Quantity</label>
                    <?= Html::input('number', 'quantities[' . $product->id . ']', 0, [
                        'id' => 'qty-' . $product->id,
                        'class' => 'form-control',
                        'min' => 0,
                        'max' => 99,
                        'step' => 1,
                        'form' => $batchFormId,
                        'aria-label' => 'Quantity of ' . $product->name . ' for the batch add',
                        'data-card-quantity' => (string) $product->id,
                    ]) ?>
                </div>
            <?php endif; ?>
            <?= Html::beginForm(Navigation::url(Navigation::CART_ADD, ['id' => $product->id]), 'POST', [
                'class' => 'position-relative z-2 mt-3 js-cart-add',
                'data-cart-ajax' => '1',
                'data-quantity-source' => '#qty-' . $product->id,
            ]) ?>
            <?= Html::hiddenInput('quantity', '1') ?>
            <?= Html::submitButton('Add to cart', ['class' => 'btn btn-primary w-100']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
        <?php if ($canUpdateProducts || $canArchiveProducts || $canDeleteProducts) : ?>
            <div class="mt-3 position-relative z-2 product-card-actions">
                <?php if ($canUpdateProducts) : ?>
                    <?= Html::a('Edit', Navigation::url(Navigation::PRODUCT_UPDATE, ['id' => $product->id]), [
                        'class' => 'btn btn-sm btn-primary',
                    ]) ?>
                <?php endif; ?>
                <?php if ($canArchiveProducts && $product->isActive()) : ?>
                    <?= Html::beginForm(
                        Navigation::url(Navigation::PRODUCT_ARCHIVE, ['id' => $product->id]),
                        'POST',
                        ['class' => 'd-inline'],
                    ) ?>
                    <?= Html::submitButton('Archive', [
                        'class' => 'btn btn-sm btn-outline-secondary',
                        'data-confirm-modal' => 'Archive this product?',
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php elseif ($canUpdateProducts && !$product->isActive()) : ?>
                    <?= Html::beginForm(
                        Navigation::url(Navigation::PRODUCT_RESTORE, ['id' => $product->id]),
                        'POST',
                        ['class' => 'd-inline'],
                    ) ?>
                    <?= Html::submitButton('Restore', [
                        'class' => 'btn btn-sm btn-outline-primary',
                        'data-confirm-modal' => 'Restore this product to the active catalog?',
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
                <?php if ($canDeleteProducts) : ?>
                    <?= Html::beginForm(
                        Navigation::url(Navigation::PRODUCT_DELETE, ['id' => $product->id]),
                        'POST',
                        ['class' => 'd-inline'],
                    ) ?>
                    <?= Html::submitButton('Delete permanently', [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data-confirm-modal' => 'Permanently delete this product? This cannot be undone.',
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($canViewAudit) : ?>
            <div class="mt-3 pt-3 text-body-secondary product-audit-summary small">
                <div class="d-flex gap-3 justify-content-between">
                    <span>Created by</span>
                    <strong class="text-end">
                        <?= Html::encode($product->createdBy?->username ?? '—') ?>
                    </strong>
                </div>
                <div class="d-flex gap-3 mt-1 justify-content-between">
                    <span>Updated by</span>
                    <strong class="text-end">
                        <?= Html::encode($product->updatedBy?->username ?? '—') ?>
                    </strong>
                </div>
                <div class="mt-2">
                    Last update:
                    <?= Yii::$app->formatter->asDatetime($product->updated_at) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</article>