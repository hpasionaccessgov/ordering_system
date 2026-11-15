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
?>

<div class="order-page">
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>

    <?= Html::a(
        'Back to orders',
        ['/order/index'],
        ['class' => 'btn btn-outline-secondary'],
    ) ?>
</div>

<section class="order-detail-card p-3 p-md-4">
<p class="mb-4">
    <span class="text-body-secondary me-2">Status</span>
    <span class="badge order-status">
        <?= Html::encode($statusLabels[$model->status] ?? $model->status) ?>
    </span>
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
        <?php foreach ($model->items as $item): ?>
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

<p class="text-end fw-bold">
    Total:
    <?= Yii::$app->formatter->asCurrency($model->total_amount) ?>
</p>

<?= $this->render('_cancel', ['model' => $model]) ?>

<?php if (
    Yii::$app->user->can('order.process')
    && in_array(
        $model->status,
        [
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
        ],
        true,
    )
): ?>
    <?= Html::beginForm(
        ['/order/process', 'id' => $model->id],
        'post',
        ['class' => 'd-inline'],
    ) ?>

    <?= Html::submitButton(
        'Update order status',
        ['class' => 'btn btn-primary'],
    ) ?>

    <?= Html::endForm() ?>
<?php endif; ?>
</section>
</div>
