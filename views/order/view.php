<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $model */

$this->title = "Order #{$model->id}";

$statusLabels = [
    Order::STATUS_PENDING => 'Pending',
    Order::STATUS_CONFIRMED => 'Confirmed',
    Order::STATUS_PROCESSING => 'Processing',
    Order::STATUS_COMPLETED => 'Completed',
    Order::STATUS_CANCELLED => 'Cancelled',
];
$staffActionLabels = [
    Order::STATUS_PENDING => 'Confirm order',
    Order::STATUS_CONFIRMED => 'Start preparing',
    Order::STATUS_PROCESSING => 'Mark completed',
];
$nextStatus = $model->nextStatus();
?>

<div class="order-page">
<div class="d-flex justify-content-between align-items-center gap-3 mb-4 order-no-print">
    <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>

    <div class="d-flex gap-2">
        <?= Html::button('Print order receipt', [
            'class' => 'btn btn-outline-primary',
            'type' => 'button',
            'onclick' => 'window.print()',
        ]) ?>
        <?= Html::a(
            'Back to orders',
            ['/order/index'],
            ['class' => 'btn btn-outline-secondary'],
        ) ?>
    </div>
</div>

<section class="order-detail-card order-receipt p-3 p-md-4" id="order-receipt">
<div class="order-receipt-heading d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Cafébara</p>
        <h1 class="h3 mb-1">Order receipt</h1>
        <p class="text-body-secondary mb-0">Order #<?= Html::encode((string) $model->id) ?></p>
    </div>
    <div class="text-end">
        <p class="mb-1">
            <span class="text-body-secondary me-2">Status</span>
            <span class="badge order-status">
                <?= Html::encode($statusLabels[$model->status] ?? $model->status) ?>
            </span>
        </p>
        <p class="small text-body-secondary mb-0">
            <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
        </p>
    </div>
</div>

<div class="order-receipt-customer mb-4">
    <span class="text-body-secondary">Customer</span>
    <strong><?= Html::encode($model->user->full_name ?: $model->user->username) ?></strong>
</div>

<p class="mb-4">
    Order details
</p>

<div class="table-responsive">
<table class="table align-middle">
    <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Unit price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($model->items as $item) : ?>
            <tr>
                <td><?= Html::encode($item->product->name) ?></td>
                <td><?= Html::encode((string) $item->quantity) ?></td>
                <td><?= Yii::$app->formatter->asCurrency($item->unit_price) ?></td>
                <td>
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

<div class="order-no-print mt-4 d-flex flex-wrap gap-2">
<?= $this->render('_cancel', ['model' => $model]) ?>

<?php if (Yii::$app->user->can('order.process') && $nextStatus !== null) : ?>
    <?= Html::beginForm(
        ['/order/process', 'id' => $model->id],
        'post',
        ['class' => 'd-inline'],
    ) ?>

    <?= Html::submitButton(
        $staffActionLabels[$model->status] ?? 'Advance order',
        ['class' => 'btn btn-primary'],
    ) ?>

    <?= Html::endForm() ?>
<?php endif; ?>
</div>
</section>
</div>
