<?php

use app\models\Product;
use app\services\ShoppingCart;
use app\widgets\ToolTip;
use yii\helpers\Html;

/** @var array{lines: list<array{product: Product, quantity: int, subtotal: string}>, total: string, count: int} $summary */

$canViewProduct = Yii::$app->user->can("product.view");

$this->title = 'Shopping cart';
?>

<section class="shopping-cart-page">
    <div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
        <div>
            <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Your selection</p>
            <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
        </div>
        <?php if (Yii::$app->user->can('product.index')) : ?>
            <?= Html::a('Continue shopping', ['/catalog/products'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>

    <?php if ($summary['lines'] === []): ?>
        <div class="card admin-panel">
            <div class="py-5 card-body text-center">
                <h2 class="h5">Your cart is empty</h2>
                <p class="text-body-secondary">Choose active products from the catalog.</p>
                <?php if (Yii::$app->user->can('product.index')) : ?>
                    <?= Html::a('Browse menu', ['/catalog/products'], ['class' => 'btn btn-primary']) ?>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-12 col-lg-8 p-3 card admin-panel cart-items-panel">
                <?php foreach ($summary['lines'] as $line): ?>
                    <?php $product = $line['product']; ?>
                    <article class="mb-3 card admin-panel">
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                <?php if ($product->getImageUrl() !== null) : ?>
                                    <?php $image = Html::img($product->getImageUrl(), [
                                        'class' => 'cart-item-image',
                                        'alt' => $product->name,
                                        'loading' => 'lazy',
                                    ]) ?>
                                    <div class="cart-item-media">
                                        <?php if ($canViewProduct) : ?>
                                            <?= Html::a($image, ['/catalog/products/' . $product->id]) ?>
                                            <div class="cart-item-overlay" aria-hidden="true">
                                                <i class="bi bi-zoom-in cart-item-overlay-icon"></i>
                                                <span class="cart-item-overlay-label">View</span>
                                            </div>
                                        <?php else : ?>
                                            <?= $image ?>
                                        <?php endif; ?>
                                    </div>
                                <?php else : ?>
                                    <div class="cart-item-placeholder" aria-hidden="true">
                                        <i class="bi bi-cup-hot"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h2 class="mb-1 h5"><?= Html::encode($product->name) ?></h2>
                                    <p class="mb-1 text-body-secondary">
                                        <?= Html::encode($product->categoryRecord?->name ?? 'Cafébara menu') ?>
                                    </p>
                                    <strong><?= Yii::$app->formatter->asCurrency($product->price) ?> each</strong>
                                </div>
                                <?php if (Yii::$app->user->can('cart.update')) : ?>
                                    <?= Html::beginForm(['/cart/update', 'id' => $product->id], 'POST', [
                                        'class' => 'd-flex align-items-end gap-2',
                                    ]) ?>
                                    <?= Html::input('number', 'quantity', $line['quantity'], [
                                        'class' => 'form-control order-quantity-input',
                                        'min' => 0,
                                        'max' => ShoppingCart::MAX_QUANTITY,
                                        'step' => 1,
                                        'aria-label' => 'Quantity of ' . $product->name,
                                    ]) ?>
                                    <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
                                    <?= Html::endForm() ?>
                                <?php endif; ?>
                                <div class="text-end">
                                    <span class="d-block text-body-secondary small">Subtotal</span>
                                    <strong><?= Yii::$app->formatter->asCurrency($line['subtotal']) ?></strong>
                                </div>
                                <?php if (Yii::$app->user->can('cart.remove')) : ?>
                                    <?= Html::beginForm(['/cart/remove', 'id' => $product->id], 'POST', [
                                        'class' => 'ms-lg-2',
                                    ]) ?>
                                    <?= Html::submitButton('Remove', [
                                        'class' => 'btn btn-sm btn-danger',
                                        'aria-label' => 'Remove ' . $product->name,
                                    ]) ?>
                                    <?= Html::endForm() ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="col-12 col-lg-4">
                <section class="card admin-panel">
                    <div class="card-body">
                        <h2 class="h5">Order summary</h2>
                        <div class="d-flex my-3 justify-content-between">
                            <span>Items</span>
                            <strong><?= Html::encode((string) $summary['count']) ?></strong>
                        </div>
                        <div class="d-flex pt-3 mb-4 justify-content-between border-top">
                            <span>Total</span>
                            <strong><?= Yii::$app->formatter->asCurrency($summary['total']) ?></strong>
                        </div>
                        <?php if (Yii::$app->user->can('cart.checkout')) : ?>
                            <?= Html::a('Continue to checkout', ['/sales/cart/checkout'], [
                                'class' => 'btn btn-primary w-100',
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    <?php endif; ?>
</section>