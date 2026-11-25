<?php

use app\models\Product;
use yii\helpers\Html;

/** @var Product $model */
/** @var array<int, int> $favoriteIds */
/** @var array{average: float, count: int}|null $ratingSummary */
/** @var list<app\models\ProductReview> $reviews */
/** @var list<app\models\Order> $eligibleOrders */

$this->title = $model->name;
$canViewProducts = Yii::$app->user->can('product.index');
$canManageProducts = Yii::$app->user->can('product.view')
    && Yii::$app->user->can('product.update');
$canDeleteProducts = Yii::$app->user->can('product.delete');
$canViewAudit = $canManageProducts
    || Yii::$app->user->can('product.archive')
    || $canDeleteProducts;
$canOrder = Yii::$app->user->can('cart.add') && $model->isActive();
$canFavorite = !Yii::$app->user->isGuest && Yii::$app->user->can('favorite.index');
$isFavorite = isset(($favoriteIds ?? [])[(int) $model->id]);
?>

<section class="mx-auto product-detail-page">
    <div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
        <div>
            <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Cafébara menu</p>
            <h1 class="mb-0 h3"><?= Html::encode($model->name) ?></h1>
        </div>
        <?php if ($canViewProducts) : ?>
            <?= Html::a(
                'Back to products',
                ['/product/index'],
                ['class' => 'btn btn-secondary'],
            ) ?>
        <?php endif; ?>
    </div>

    <div class="row g-4 align-items-stretch">
        <div class="col-12 col-lg-7">
            <figure class="mb-0 product-detail-media">
                <?php if ($model->getImageUrl() !== null) : ?>
                    <?= Html::img($model->getImageUrl(), [
                        'class' => 'product-detail-image',
                        'alt' => $model->name,
                        'loading' => 'lazy',
                    ]) ?>
                <?php else : ?>
                    <div class="product-detail-placeholder" aria-hidden="true">
                        <i class="bi bi-cup-hot"></i>
                    </div>
                <?php endif; ?>
            </figure>
        </div>

        <div class="col-12 col-lg-5">
            <section class="card h-100 admin-panel product-detail-info">
                <div class="d-flex flex-column p-4 p-xl-5 card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge rounded-pill text-bg-secondary">
                            <?= Html::encode($model->categoryRecord?->name ?? 'Cafébara menu') ?>
                        </span>
                        <?php if ($model->is_featured) : ?>
                            <span class="badge rounded-pill product-featured-badge">
                                <i class="me-1" aria-hidden="true"></i>Featured
                            </span>
                        <?php endif; ?>
                        <?php if ($canManageProducts && !$model->isActive()) : ?>
                            <span class="badge text-bg-warning">Archived</span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <?= $this->render('../shared/_rating_stars', ['summary' => $ratingSummary ?? null]) ?>
                    </div>

                    <h2 class="fw-bold h3"><?= Html::encode($model->name) ?></h2>
                    <p class="text-body-secondary product-detail-description">
                        <?= nl2br(Html::encode($model->description ?? '')) ?>
                    </p>

                    <div class="mt-auto product-detail-price">
                        <span class="text-body-secondary small">Price</span>
                        <strong><?= Yii::$app->formatter->asCurrency($model->price) ?></strong>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <?php if ($canOrder) : ?>
                            <div class="mt-4 product-order-panel">
                                <div class="d-flex flex-wrap gap-2 mb-3 justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-1 fw-bold h6">Order this item</h3>
                                        <p class="mb-0 text-body-secondary small">
                                            Choose a quantity; only <?= Html::encode($model->name) ?> will be added.
                                        </p>
                                    </div>
                                    <strong class="product-order-unit-price">
                                        <?= Yii::$app->formatter->asCurrency($model->price) ?> each
                                    </strong>
                                </div>
                                <?= Html::beginForm(
                                    ['/cart/add', 'id' => $model->id],
                                    'POST',
                                    ['class' => 'd-flex flex-wrap align-items-end gap-3'],
                                ) ?>
                                <div>
                                    <?= Html::label('Quantity', 'product-order-quantity', [
                                        'class' => 'form-label small fw-semibold',
                                    ]) ?>
                                    <?= Html::input('number', 'quantity', 1, [
                                        'id' => 'product-order-quantity',
                                        'class' => 'form-control order-quantity-input',
                                        'min' => 1,
                                        'step' => 1,
                                        'required' => true,
                                        'inputmode' => 'numeric',
                                        'aria-label' => 'Quantity of ' . $model->name,
                                    ]) ?>
                                </div>
                                <?= Html::submitButton('Cart', ['class' => 'btn btn-primary']) ?>
                                <?= Html::endForm() ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($canFavorite && $model->isActive()) : ?>
                            <?= Html::beginForm(
                                [$isFavorite ? '/favorite/remove' : '/favorite/add', 'id' => $model->id],
                                'POST',
                            ) ?>
                            <?= Html::submitButton(
                                '<i class="bi ' . ($isFavorite ? 'bi-heart-fill' : 'bi-heart') . ' me-1" aria-hidden="true"></i>'
                                . ($isFavorite ? 'Remove from favorites' : 'Add to favorites'),
                                ['class' => 'btn btn-primary', 'encode' => false],
                            ) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>

                        <?php if ($canManageProducts) : ?>
                            <?= Html::a('Edit product', ['/product/update', 'id' => $model->id], [
                                'class' => 'btn btn-primary',
                            ]) ?>
                        <?php endif; ?>

                        <?php if (
                            Yii::$app->user->can('product.archive')
                            && $model->isActive()
                        ) : ?>
                            <?= Html::beginForm(
                                ['/product/archive', 'id' => $model->id],
                                'POST',
                                ['class' => 'd-inline'],
                            ) ?>
                            <?= Html::submitButton('Archive product', [
                                'class' => 'btn btn-danger',
                                'data-confirm-modal' => 'Archive this product?',
                            ]) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>

                        <?php if ($canDeleteProducts) : ?>
                            <?= Html::beginForm(
                                ['/product/delete', 'id' => $model->id],
                                'POST',
                                ['class' => 'd-inline'],
                            ) ?>
                            <?= Html::submitButton('Delete permanently', [
                                'class' => 'btn btn-danger',
                                'data-confirm-modal' => 'Permanently delete this product? This cannot be undone.',
                            ]) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($canViewAudit) : ?>
                        <div class="mt-4 pt-4 text-body-secondary product-audit-summary small">
                            <div class="row g-3">
                                <div class="col-6">
                                    <span class="d-block">Created by</span>
                                    <strong class="text-body">
                                        <?= Html::encode($model->createdBy?->username ?? '—') ?>
                                    </strong>
                                    <span class="d-block mt-1">
                                        <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                                    </span>
                                </div>
                                <div class="col-6">
                                    <span class="d-block">Updated by</span>
                                    <strong class="text-body">
                                        <?= Html::encode($model->updatedBy?->username ?? '—') ?>
                                    </strong>
                                    <span class="d-block mt-1">
                                        <?= Yii::$app->formatter->asDatetime($model->updated_at) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</section>

<section class="mt-4 card admin-panel product-reviews-panel">
    <div class="card-header bg-transparent">
        <h2 class="mb-0 h5">Customer reviews</h2>
    </div>
    <div class="card-body">
        <?php $reviews = $reviews ?? []; ?>
        <?php if ($reviews === []) : ?>
            <p class="mb-0 text-body-secondary">No reviews yet.</p>
        <?php else : ?>
            <?php foreach ($reviews as $review) : ?>
                <?= $this->render('../shared/_review_item', ['review' => $review]) ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (($eligibleOrders ?? []) !== []) : ?>
            <?= $this->render('../shared/_review_form', [
                'model' => $model,
                'eligibleOrders' => $eligibleOrders,
            ]) ?>
        <?php endif; ?>
    </div>
</section>
