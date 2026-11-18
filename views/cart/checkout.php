<?php

use app\models\Product;
use yii\helpers\Html;

/** @var array{lines: list<array{product: Product, quantity: int, subtotal: string}>, total: string, count: int} $summary */
/** @var string $checkoutToken */

$this->title = 'Checkout';
?>

<section class="checkout-page">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Review order</p>
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <section class="card admin-panel">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0 cafebara-data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary['lines'] as $line): ?>
                                <tr>
                                    <td><?= Html::encode($line['product']->name) ?></td>
                                    <td><?= Html::encode((string) $line['quantity']) ?></td>
                                    <td><?= Yii::$app->formatter->asCurrency($line['product']->price) ?></td>
                                    <td class="text-end"><?= Yii::$app->formatter->asCurrency($line['subtotal']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
        <div class="col-12 col-lg-4">
            <section class="card admin-panel">
                <div class="card-body">
                    <h2 class="h5">Final total</h2>
                    <strong class="d-block fs-3 my-3">
                        <?= Yii::$app->formatter->asCurrency($summary['total']) ?>
                    </strong>
                    <p class="small text-body-secondary">
                        Prices are validated again when you place the order. This system records orders, not payment.
                    </p>
                    <?= Html::beginForm(['/cart/checkout'], 'post') ?>
                    <?= Html::hiddenInput('checkout_token', $checkoutToken) ?>
                    <?= Html::submitButton('Place order', ['class' => 'btn btn-primary w-100']) ?>
                    <?= Html::endForm() ?>
                    <?= Html::a('Back to cart', ['/cart/index'], [
                        'class' => 'btn btn-outline-secondary w-100 mt-2',
                    ]) ?>
                </div>
            </section>
        </div>
    </div>
</section>
