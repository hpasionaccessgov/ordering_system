<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $model */

$this->title = "Receipt for {$model->order_number}";
?>

<div class="receipt-print-page">
    <div class="d-flex flex-wrap gap-2 mb-4 justify-content-between receipt-actions">
        <?= Html::a(
            'Back to order',
            ['/sales/orders/' . $model->id],
            ['class' => 'btn btn-secondary'],
        ) ?>
        <?= Html::button('Print receipt', [
            'class' => 'btn btn-primary',
            'type' => 'button',
            'onclick' => 'window.print()',
        ]) ?>
    </div>

    <article class="order-detail-card order-receipt p-3 p-md-4" id="order-receipt">
        <header class="order-receipt-heading d-flex gap-3 mb-4 justify-content-between align-items-center">
            <?= Html::img(
                Yii::getAlias('@web/Cafebara_logo.png'),
                [
                    'class' => 'order-receipt-logo',
                    'alt' => 'Cafébara',
                ],
            ) ?>
            <div class="text-end">
                <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">
                    Order receipt
                </p>
                <h1 class="mb-1 h4"><?= Html::encode($model->order_number) ?></h1>
                <span class="order-status badge">
                    <?= Html::encode(Order::statusLabel((string) $model->status)) ?>
                </span>
            </div>
        </header>

        <div class="order-receipt-meta mb-4">
            <div>
                <span class="text-body-secondary">Customer</span>
                <strong><?= Html::encode($model->user->full_name ?: $model->user->username) ?></strong>
            </div>
            <div>
                <span class="text-body-secondary">Placed</span>
                <strong><?= Yii::$app->formatter->asDatetime($model->created_at) ?></strong>
            </div>
        </div>

        <h2 class="mb-3 h6">Order details</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle cafebara-data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit price</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->items as $item) : ?>
                        <tr>
                            <td><?= Html::encode($item->product_name) ?></td>
                            <td><?= Html::encode((string) $item->quantity) ?></td>
                            <td><?= Yii::$app->formatter->asCurrency($item->unit_price) ?></td>
                            <td class="text-end">
                                <?= Yii::$app->formatter->asCurrency(
                                    $item->subtotal,
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="order-receipt-total d-flex justify-content-between">
            <span>Total</span>
            <strong><?= Yii::$app->formatter->asCurrency($model->total_amount) ?></strong>
        </div>
        <p class="mt-3 mb-0 text-body-secondary small">
            This receipt confirms the order and fulfillment status; payment is not recorded by this system.
        </p>
    </article>
</div>
