<?php

use app\services\Navigation;
use app\models\Product;
use yii\helpers\Html;

/** @var Product[] $products */
/** @var array<int, int> $quantities */
/** @var array|string $formAction */
/** @var string $submitLabel */

$initialTotal = 0.0;
$initialQuantity = 0;
foreach ($products as $product) {
    $quantity = max(0, (int) ($quantities[$product->id] ?? 0));
    $initialQuantity += $quantity;
    $initialTotal += (float) $product->price * $quantity;
}

$this->registerJs(<<<'JS'
document.querySelectorAll('[data-order-builder]').forEach((form) => {
    const quantityInputs = form.querySelectorAll('[data-order-quantity]');
    const totalElement = form.querySelector('[data-order-total]');
    const countElement = form.querySelector('[data-order-count]');
    const submitButton = form.querySelector('[data-order-submit]');
    const formatter = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    });

    const updateSummary = () => {
        let total = 0;
        let count = 0;

        quantityInputs.forEach((input) => {
            const quantity = Math.max(0, Number.parseInt(input.value, 10) || 0);
            const lineTotal = quantity * Number(input.dataset.price);
            total += lineTotal;
            count += quantity;
            input.closest('[data-order-product]').querySelector('[data-line-total]').textContent =
                formatter.format(lineTotal);
        });

        totalElement.textContent = formatter.format(total);
        countElement.textContent = String(count);
        submitButton.disabled = count === 0;
    };

    quantityInputs.forEach((input) => input.addEventListener('input', updateSummary));
    updateSummary();
});
JS);
?>

<?= Html::beginForm($formAction, 'post', [
    'class' => 'order-builder',
    'data-order-builder' => 'true',
]) ?>

<div class="order-builder-summary mb-4 card admin-panel">
    <div class="d-flex flex-wrap gap-3 card-body justify-content-between align-items-center">
        <div>
            <p class="mb-1 text-body-secondary small">Items selected</p>
            <strong><span data-order-count><?= $initialQuantity ?></span></strong>
        </div>
        <div class="text-end">
            <p class="mb-1 text-body-secondary small">Order total</p>
            <strong class="order-builder-total" data-order-total>
                <?= Yii::$app->formatter->asCurrency($initialTotal) ?>
            </strong>
        </div>
        <?= Html::submitButton($submitLabel, [
            'class' => 'btn btn-primary',
            'data-order-submit' => 'true',
            'disabled' => $initialQuantity === 0,
        ]) ?>
    </div>
</div>

<div class="row g-3">
    <?php foreach ($products as $product) : ?>
        <?php $quantity = max(0, (int) ($quantities[$product->id] ?? 0)); ?>
        <?php $canViewProduct = Yii::$app->user->can('product.view'); ?>
        <div class="col-12 col-md-6 col-xl-4">
            <article
                class="order-product-card card h-100<?= $canViewProduct ? 'position-relative ' : '' ?>"
                data-order-product
                data-price="<?= Html::encode((string) $product->price) ?>">
                <div class="order-product-media">
                    <?php if ($product->getImageUrl() !== null) : ?>
                        <?= Html::img($product->getImageUrl(), [
                            'class' => 'order-product-image',
                            'alt' => $product->name,
                            'loading' => 'lazy',
                        ]) ?>
                    <?php else : ?>
                        <div class="order-product-placeholder" aria-hidden="true">
                            <i class="bi bi-cup-hot"></i>
                        </div>
                    <?php endif; ?>
                    <?php if ($canViewProduct) : ?>
                        <div class="product-card-overlay" aria-hidden="true">
                            <i class="bi bi-zoom-in product-card-overlay-icon"></i>
                            <span class="product-card-overlay-label">View</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex flex-column card-body">
                    <p class="mb-2 product-category">
                        <?= Html::encode($product->categoryRecord?->name ?? 'Cafébara menu') ?>
                    </p>
                    <h2 class="h5">
                        <?php if ($canViewProduct) : ?>
                            <?= Html::a(Html::encode($product->name), Navigation::url(Navigation::PRODUCT_VIEW, ['id' => $product->id]), [
                                'class' => 'stretched-link link-body-emphasis text-decoration-none',
                            ]) ?>
                        <?php else : ?>
                            <?= Html::encode($product->name) ?>
                        <?php endif; ?>
                    </h2>
                    <p class="flex-grow-1 text-body-secondary small">
                        <?= Html::encode($product->description ?? '') ?>
                    </p>
                    <div class="d-flex gap-2 mb-3 justify-content-between align-items-center">
                        <span class="text-body-secondary">Unit price</span>
                        <strong><?= Yii::$app->formatter->asCurrency($product->price) ?></strong>
                    </div>

                    <?= Html::hiddenInput(
                        "items[{$product->id}][product_id]",
                        (string) $product->id,
                    ) ?>
                    <div class="d-flex gap-3 justify-content-between align-items-end position-relative z-2">
                        <?= Html::label(
                            'Quantity',
                            "quantity-{$product->id}",
                            ['class' => 'form-label mb-0'],
                        ) ?>
                        <?= Html::input('number', "items[{$product->id}][quantity]", $quantity, [
                            'id' => "quantity-{$product->id}",
                            'class' => 'form-control order-quantity-input',
                            'min' => 0,
                            'step' => 1,
                            'inputmode' => 'numeric',
                            'data-order-quantity' => 'true',
                            'data-price' => (string) $product->price,
                            'aria-label' => 'Quantity of ' . $product->name,
                        ]) ?>
                    </div>
                    <div class="d-flex mt-3 pt-3 justify-content-between align-items-center border-top">
                        <span class="text-body-secondary small">Item subtotal</span>
                        <strong data-line-total>
                            <?= Yii::$app->formatter->asCurrency((float) $product->price * $quantity) ?>
                        </strong>
                    </div>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($products === []) : ?>
    <div class="alert alert-info" role="status">There are no active products available to order.</div>
<?php endif; ?>

<?= Html::endForm() ?>
