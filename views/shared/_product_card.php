<?php

use app\models\Product;
use yii\helpers\Html;

/** @var Product $product */

$canViewProduct = Yii::$app->user->isGuest || Yii::$app->user->can('product.view');
$canUpdateProducts = $canViewProduct && Yii::$app->user->can('product.update');
$canArchiveProducts = Yii::$app->user->can('product.archive');
$canDeleteProducts = Yii::$app->user->can('product.delete');
?>

<article class="card product-card product-catalog-card h-100 position-relative">
    <div class="product-card-media">
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
    </div>

    <div class="card-body d-flex flex-column">
        <p class="product-category mb-2">
            <?= Html::encode($product->categoryRecord?->name ?? 'Cafébara menu') ?>
        </p>
        <h2 class="h5 mb-2">
            <?php if ($canViewProduct) : ?>
                <?= Html::a(Html::encode($product->name), ['/product/view', 'id' => $product->id], [
                    'class' => 'stretched-link link-body-emphasis text-decoration-none',
                ]) ?>
            <?php else : ?>
                <?= Html::encode($product->name) ?>
            <?php endif; ?>
        </h2>
        <p class="text-body-secondary small mb-3">
            <?= Html::encode($product->description ?? '') ?>
        </p>
        <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
            <strong class="product-card-price"><?= Yii::$app->formatter->asCurrency($product->price) ?></strong>
            <?php if ($canViewProduct) : ?>
                <span class="small text-primary fw-semibold">View details</span>
            <?php endif; ?>
        </div>
        <?php if (
            !Yii::$app->user->isGuest
            && Yii::$app->user->can('cart.add')
            && $product->isActive()
        ) : ?>
            <?= Html::beginForm(['/cart/add', 'id' => $product->id], 'post', [
                'class' => 'd-flex gap-2 position-relative z-2 mt-3',
            ]) ?>
            <?= Html::input('number', 'quantity', 1, [
                'class' => 'form-control',
                'min' => 1,
                'max' => 99,
                'step' => 1,
                'aria-label' => 'Quantity of ' . $product->name,
            ]) ?>
            <?= Html::submitButton('Add to cart', ['class' => 'btn btn-primary']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
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
