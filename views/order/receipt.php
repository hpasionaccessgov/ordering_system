<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $model */

$this->title = "Receipt for Order #{$model->id}";
$statusLabels = [
    Order::STATUS_PENDING => 'Pending',
    Order::STATUS_CONFIRMED => 'Confirmed',
    Order::STATUS_PROCESSING => 'Processing',
    Order::STATUS_COMPLETED => 'Completed',
    Order::STATUS_CANCELLED => 'Cancelled',
];
?>

<div class="receipt-print-page">
    <div class="receipt-actions d-flex flex-wrap justify-content-between gap-2 mb-4">
        <?= Html::a(
            'Back to order',
            ['/order/view', 'id' => $model->id],
            ['class' => 'btn btn-outline-secondary'],
        ) ?>
        <?= Html::button('Print receipt', [
            'class' => 'btn btn-primary',
            'type' => 'button',
            'onclick' => 'window.print()',
        ]) ?>
    </div>

    <article class="order-detail-card order-receipt p-3 p-md-4" id="order-receipt">
        <header class="order-receipt-heading d-flex justify-content-between align-items-center gap-3 mb-4">
            <?= Html::img(
                Yii::getAlias('@web/images/Cafebara_logo.png'),
                [
                    'class' => 'order-receipt-logo',
                    'alt' => 'Cafébara',
                ],
            ) ?>
            <div class="text-end">
                <p class="text-uppercase small fw-semibold text-body-secondary mb-1">
                    Order receipt
                </p>
                <h1 class="h4 mb-1">Order #<?= Html::encode((string) $model->id) ?></h1>
                <span class="badge order-status">
                    <?= Html::encode($statusLabels[$model->status] ?? $model->status) ?>
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

        <h2 class="h6 mb-3">Order details</h2>
        <div class="table-responsive">
            <table class="table align-middle">
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
                            <td><?= Html::encode($item->product->name) ?></td>
                            <td><?= Html::encode((string) $item->quantity) ?></td>
                            <td><?= Yii::$app->formatter->asCurrency($item->unit_price) ?></td>
                            <td class="text-end">
                                <?= Yii::$app->formatter->asCurrency(
                                    (float) $item->unit_price * $item->quantity,
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
        <p class="small text-body-secondary mt-3 mb-0">
            This receipt confirms the order and fulfillment status; payment is not recorded by this system.
        </p>
    </article>
</div>
