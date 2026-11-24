<?php

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
?>

<article class="card h-100 position-relative product-card product-catalog-card">
    <div class="product-card-media">
        <?php if ($product->getImageUrl() !== null) : ?>
            <?= Html::img($product->getImageUrl(), [
                'class' => 'product-card-image',
                'alt' => $product->name,
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
        <?php if ($canFavorite) : ?>
            <?php
            $favoriteLabel = $isFavorite ? 'Remove from favorites' : 'Add to favorites';
            ?>
            <?= Html::beginForm(
                [$isFavorite ? '/favorite/remove' : '/favorite/add', 'id' => $product->id],
                'POST',
                ['class' => 'product-card-favorite'],
            ) ?>
            <?= Html::submitButton('<i class="bi ' . ($isFavorite ? 'bi-heart-fill' : 'bi-heart') . '" aria-hidden="true"></i>', [
                'class' => 'btn btn-sm btn-light rounded-circle shadow-sm',
                'encode' => false,
                'aria-label' => $favoriteLabel,
                'aria-pressed' => $isFavorite ? 'true' : 'false',
                'title' => $favoriteLabel,
            ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>

    <div class="d-flex flex-column card-body">
        <p class="mb-2 product-category">
            <?= Html::encode($product->categoryRecord?->name ?? 'Cafébara menu') ?>
        </p>
        <?= $this->render('_rating_stars', [
            'summary' => $ratingSummaries[(int) $product->id] ?? null,
        ]) ?>
        <h2 class="mb-2 h5">
            <?php if ($canViewProduct) : ?>
                <?= Html::a(Html::encode($product->name), ['/product/view', 'id' => $product->id], [
                    'class' => 'stretched-link link-body-emphasis text-decoration-none',
                ]) ?>
            <?php else : ?>
                <?= Html::encode($product->name) ?>
            <?php endif; ?>
        </h2>
        <p class="mb-3 text-body-secondary small">
            <?= Html::encode($product->description ?? '') ?>
        </p>
        <div class="d-flex mt-auto gap-2 justify-content-between align-items-center">
            <strong class="product-card-price"><?= Yii::$app->formatter->asCurrency($product->price) ?></strong>
            <?php if ($canViewProduct) : ?>
                <span class="text-primary fw-semibold small">Click to view details</span>
            <?php endif; ?>
        </div>
        <?php
        $canAddToCart = Yii::$app->user->isGuest || Yii::$app->user->can('cart.add');
        ?>
        <?php if ($canAddToCart && $product->isActive()) : ?>
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
                    ]) ?>
                </div>
            <?php endif; ?>
            <?= Html::beginForm(['/cart/add', 'id' => $product->id], 'POST', [
                'class' => 'position-relative z-2 mt-3',
            ]) ?>
            <?= Html::hiddenInput('quantity', '1') ?>
            <?= Html::submitButton('Add to cart', ['class' => 'btn btn-primary w-100']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
        <?php if ($canUpdateProducts || $canArchiveProducts || $canDeleteProducts) : ?>
            <div class="mt-3 position-relative z-2 product-card-actions">
                <?php if ($canUpdateProducts) : ?>
                    <?= Html::a('Edit', ['/product/update', 'id' => $product->id], [
                        'class' => 'btn btn-sm btn-outline-primary',
                    ]) ?>
                <?php endif; ?>
                <?php if ($canArchiveProducts && $product->isActive()) : ?>
                    <?= Html::beginForm(
                        ['/product/archive', 'id' => $product->id],
                        'POST',
                        ['class' => 'd-inline'],
                    ) ?>
                    <?= Html::submitButton('Archive', [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data-confirm-modal' => 'Archive this product?',
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php elseif ($canUpdateProducts && !$product->isActive()) : ?>
                    <?= Html::beginForm(
                        ['/product/restore', 'id' => $product->id],
                        'POST',
                        ['class' => 'd-inline'],
                    ) ?>
                    <?= Html::submitButton('Restore', [
                        'class' => 'btn btn-sm btn-outline-success',
                        'data-confirm-modal' => 'Restore this product to the active catalog?',
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
                <?php if ($canDeleteProducts) : ?>
                    <?= Html::beginForm(
                        ['/product/delete', 'id' => $product->id],
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
