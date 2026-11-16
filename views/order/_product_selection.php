<?php

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

<div class="order-builder-summary card admin-panel mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <p class="text-body-secondary small mb-1">Items selected</p>
            <strong><span data-order-count><?= $initialQuantity ?></span></strong>
        </div>
        <div class="text-end">
            <p class="text-body-secondary small mb-1">Order total</p>
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
        <div class="col-12 col-md-6 col-xl-4">
            <article
                class="card order-product-card h-100"
                data-order-product
                data-price="<?= Html::encode((string) $product->price) ?>">
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

                <div class="card-body d-flex flex-column">
                    <p class="product-category mb-2"><?= Html::encode($product->category ?: 'Cafébara menu') ?></p>
                    <h2 class="h5"><?= Html::encode($product->name) ?></h2>
                    <p class="text-body-secondary small flex-grow-1">
                        <?= Html::encode($product->description ?? '') ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <span class="text-body-secondary">Unit price</span>
                        <strong><?= Yii::$app->formatter->asCurrency($product->price) ?></strong>
                    </div>

                    <?= Html::hiddenInput(
                        "items[{$product->id}][product_id]",
                        (string) $product->id,
                    ) ?>
                    <div class="d-flex justify-content-between align-items-end gap-3">
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
                    <div class="d-flex justify-content-between align-items-center border-top mt-3 pt-3">
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
