<?php

declare(strict_types=1);

use app\services\Navigation;
use app\services\ShoppingCart;
use yii\helpers\Html;

/** @var array{lines: list<array{product: app\models\Product, quantity: int, subtotal: string}>, total: string, count: int} $summary */

$summary = $summary ?? (new ShoppingCart())->summary();
$canUpdate = Yii::$app->user->can('cart.update');
$canRemove = Yii::$app->user->can('cart.remove');
?>
<div class="d-flex gap-2 mb-3 justify-content-between align-items-center">
    <span class="text-body-secondary small"><?= (int) $summary['count'] ?> item(s)</span>
    <strong class="small">Total <?= Yii::$app->formatter->asCurrency($summary['total']) ?></strong>
</div>

<div class="mb-2 text-success cafebara-cart-feedback small" data-cart-feedback aria-live="polite"></div>

<?php if ($summary['lines'] === []) : ?>
    <div class="py-5 text-center cafebara-empty-state">
        <span class="cafebara-empty-icon" aria-hidden="true"><i class="bi bi-cup-hot"></i></span>
        <h2 class="mt-3 h5">Your cart is waiting for its first drink.</h2>
        <p class="mb-3 text-body-secondary">Browse our menu to get started.</p>
        <?= Html::a('Browse Menu', Navigation::url(Navigation::PRODUCT_INDEX), ['class' => 'btn btn-primary']) ?>
    </div>
<?php else : ?>
    <?php foreach ($summary['lines'] as $line) : ?>
        <?php $product = $line['product']; ?>
        <article class="d-flex gap-3 p-3 mb-3 cafebara-cart-line">
            <?php if ($product->getImageUrl() !== null) : ?>
                <?= Html::img($product->getImageUrl(), [
                    'class' => 'cafebara-cart-line-image',
                    'alt' => '',
                    'loading' => 'lazy',
                ]) ?>
            <?php else : ?>
                <span class="d-inline-flex align-items-center justify-content-center bg-body-secondary text-body-secondary cafebara-cart-line-image" aria-hidden="true">
                    <i class="bi bi-cup-hot"></i>
                </span>
            <?php endif; ?>

            <div class="flex-grow-1">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
                    <strong><?= Html::encode($product->name) ?></strong>
                    <strong><?= Yii::$app->formatter->asCurrency($line['subtotal']) ?></strong>
                </div>
                <p class="mb-1 text-body-secondary small">
                    <?= Yii::$app->formatter->asCurrency($product->price) ?> each
                </p>
                <div class="d-flex gap-2 mt-2 align-items-center">
                    <?php if ($canUpdate) : ?>
                        <?= Html::beginForm(Navigation::url(Navigation::CART_UPDATE_QUANTITY, ['id' => $product->id]), 'POST', [
                            'class' => 'd-flex gap-2 align-items-center',
                            'data-cart-ajax' => '1',
                        ]) ?>
                        <?= Html::submitButton('<i class="bi bi-dash" aria-hidden="true"></i>', [
                            'class' => 'btn btn-sm btn-outline-secondary px-2',
                            'encode' => false,
                            'name' => 'quantity',
                            'value' => (string) max(0, $line['quantity'] - 1),
                            'aria-label' => 'Decrease quantity of ' . $product->name,
                        ]) ?>
                        <span class="cafebara-cart-qty"><?= (int) $line['quantity'] ?></span>
                        <?= Html::submitButton('<i class="bi bi-plus" aria-hidden="true"></i>', [
                            'class' => 'btn btn-sm btn-outline-primary px-2',
                            'encode' => false,
                            'name' => 'quantity',
                            'value' => (string) min(ShoppingCart::MAX_QUANTITY, $line['quantity'] + 1),
                            'disabled' => $line['quantity'] >= ShoppingCart::MAX_QUANTITY,
                            'aria-label' => 'Increase quantity of ' . $product->name,
                        ]) ?>
                        <?= Html::endForm() ?>
                    <?php else : ?>
                        <span class="cafebara-cart-qty">Qty <?= (int) $line['quantity'] ?></span>
                    <?php endif; ?>
                    <?php if ($canRemove) : ?>
                        <?= Html::beginForm(Navigation::url(Navigation::CART_REMOVE, ['id' => $product->id]), 'POST', [
                            'data-cart-ajax' => '1',
                        ]) ?>
                        <?= Html::submitButton('<i class="bi bi-trash3" aria-hidden="true"></i>', [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'encode' => false,
                            'aria-label' => 'Remove ' . $product->name,
                        ]) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    <?php endforeach; ?>

    <div class="d-flex pt-3 mb-3 justify-content-between border-top">
        <span>Total</span>
        <strong><?= Yii::$app->formatter->asCurrency($summary['total']) ?></strong>
    </div>
    <div class="d-grid gap-2">
        <?= Html::a('View Cart', Navigation::url(Navigation::CART_INDEX), ['class' => 'btn btn-outline-secondary']) ?>
        <?php if (Yii::$app->user->can('cart.checkout')) : ?>
            <?= Html::a('Checkout', Navigation::url(Navigation::CART_CHECKOUT), ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
