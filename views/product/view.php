<?php

use app\models\Product;
use app\services\RbacPermissionPolicy;
use yii\helpers\Html;

/** @var Product $model */

$this->title = $model->name;
$staffAccessEnabled = !RbacPermissionPolicy::isStaffAccessDisabledForCurrentUser();
$canViewProducts = Yii::$app->user->can('product.index');
$canManageProducts = $staffAccessEnabled
    && Yii::$app->user->can('product.view')
    && Yii::$app->user->can('product.update');
$canDeleteProducts = $staffAccessEnabled && Yii::$app->user->can('product.delete');
$canOrder = $staffAccessEnabled && Yii::$app->user->can('order.create') && $model->isActive();
?>

<section class="product-detail-page mx-auto">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Cafébara menu</p>
            <h1 class="h3 mb-0"><?= Html::encode($model->name) ?></h1>
        </div>
        <?php if ($canViewProducts) : ?>
            <?= Html::a(
                'Back to products',
                ['/product/index'],
                ['class' => 'btn btn-outline-secondary'],
            ) ?>
        <?php endif; ?>
    </div>

    <div class="row g-4 align-items-stretch">
        <div class="col-12 col-lg-7">
            <figure class="product-detail-media mb-0">
                <?php if ($model->getImageUrl() !== null) : ?>
                    <?= Html::img($model->getImageUrl(), [
                        'class' => 'product-detail-image',
                        'alt' => $model->name,
                    ]) ?>
                <?php else : ?>
                    <div class="product-detail-placeholder" aria-hidden="true">
                        <i class="bi bi-cup-hot"></i>
                    </div>
                <?php endif; ?>
            </figure>
        </div>

        <div class="col-12 col-lg-5">
            <section class="card admin-panel product-detail-info h-100">
                <div class="card-body d-flex flex-column p-4 p-xl-5">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge rounded-pill text-bg-secondary">
                            <?= Html::encode($model->category) ?>
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

                    <h2 class="h3 fw-bold"><?= Html::encode($model->name) ?></h2>
                    <p class="text-body-secondary product-detail-description">
                        <?= nl2br(Html::encode($model->description ?? '')) ?>
                    </p>

                    <div class="product-detail-price mt-auto">
                        <span class="text-body-secondary small">Price</span>
                        <strong><?= Yii::$app->formatter->asCurrency($model->price) ?></strong>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <?php if ($canOrder) : ?>
                            <div class="product-order-panel mt-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <h3 class="h6 fw-bold mb-1">Order this item</h3>
                                        <p class="small text-body-secondary mb-0">
                                            Choose a quantity; only <?= Html::encode($model->name) ?> will be added.
                                        </p>
                                    </div>
                                    <strong class="product-order-unit-price">
                                        <?= Yii::$app->formatter->asCurrency($model->price) ?> each
                                    </strong>
                                </div>
                                <?= Html::beginForm(
                                    ['/order/create-product', 'id' => $model->id],
                                    'post',
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
                                <?= Html::submitButton('Start order', ['class' => 'btn btn-primary']) ?>
                                <?= Html::endForm() ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($canManageProducts) : ?>
                            <?= Html::a('Edit product', ['/product/update', 'id' => $model->id], [
                                'class' => 'btn btn-outline-primary',
                            ]) ?>
                        <?php endif; ?>

                        <?php if (
                            $staffAccessEnabled
                            && Yii::$app->user->can('product.archive')
                            && $model->isActive()
                        ) : ?>
                            <?= Html::beginForm(
                                ['/product/archive', 'id' => $model->id],
                                'post',
                                ['class' => 'd-inline'],
                            ) ?>
                            <?= Html::submitButton('Archive product', [
                                'class' => 'btn btn-outline-danger',
                                'data-confirm-modal' => 'Archive this product?',
                            ]) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>

                        <?php if ($canDeleteProducts) : ?>
                            <?= Html::beginForm(
                                ['/product/delete', 'id' => $model->id],
                                'post',
                                ['class' => 'd-inline'],
                            ) ?>
                            <?= Html::submitButton('Delete permanently', [
                                'class' => 'btn btn-outline-danger',
                                'data-confirm-modal' => 'Permanently delete this product? This cannot be undone.',
                            ]) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>
