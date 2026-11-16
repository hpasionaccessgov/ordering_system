<?php

use app\models\Product;
use app\services\RbacPermissionPolicy;
use yii\helpers\Html;

/** @var Product $product */

$canManageProductActions = !RbacPermissionPolicy::isStaffAccessDisabledForCurrentUser();
$canUpdateProducts = $canManageProductActions && Yii::$app->user->can('product.update');
$canArchiveProducts = $canManageProductActions && Yii::$app->user->can('product.archive');
$canDeleteProducts = $canManageProductActions && Yii::$app->user->can('product.delete');
?>

<article class="card product-card product-catalog-card h-100 position-relative">
    <?php if ($product->getImageUrl() !== null) : ?>
        <?= Html::img($product->getImageUrl(), [
            'class' => 'product-card-image',
            'alt' => $product->name,
            'loading' => 'lazy',
        ]) ?>
    <?php else : ?>
        <div class="product-card-placeholder" aria-hidden="true">
            <i class="bi bi-cup-hot"></i>
        </div>
    <?php endif; ?>

    <div class="card-body d-flex flex-column">
        <p class="product-category mb-2"><?= Html::encode($product->category ?: 'Cafébara menu') ?></p>
        <h2 class="h5 mb-2">
            <?= Html::a(Html::encode($product->name), ['/product/view', 'id' => $product->id], [
                'class' => 'stretched-link link-body-emphasis text-decoration-none',
            ]) ?>
        </h2>
        <p class="text-body-secondary small mb-3">
            <?= Html::encode($product->description ?? '') ?>
        </p>
        <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
            <strong class="product-card-price"><?= Yii::$app->formatter->asCurrency($product->price) ?></strong>
            <span class="small text-primary fw-semibold">View details</span>
        </div>
        <?php if ($canUpdateProducts || $canArchiveProducts || $canDeleteProducts) : ?>
            <div class="product-card-actions position-relative z-2 mt-3">
                <?php if ($canUpdateProducts) : ?>
                    <?= Html::a('Edit', ['/product/update', 'id' => $product->id], [
                        'class' => 'btn btn-sm btn-outline-primary',
                    ]) ?>
                <?php endif; ?>
                <?php if ($canArchiveProducts && $product->isActive()) : ?>
                    <?= Html::beginForm(
                        ['/product/archive', 'id' => $product->id],
                        'post',
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
                        'post',
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
                        'post',
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
    </div>
</article>
