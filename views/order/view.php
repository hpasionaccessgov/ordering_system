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

<section class="order-detail-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Order details</p>
            <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (
                Yii::$app->user->can('order.view')
                && Yii::$app->user->can('order.receipt.print')
            ) : ?>
                <?= Html::a(
                    'View printable receipt',
                    ['/order/receipt', 'id' => $model->id],
                    ['class' => 'btn btn-outline-primary'],
                ) ?>
            <?php endif; ?>
            <?php if (Yii::$app->user->can('order.index')) : ?>
                <?= Html::a(
                    'Back to orders',
                    ['/order/index'],
                    ['class' => 'btn btn-outline-secondary'],
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <section class="card admin-panel order-detail-panel mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <span class="d-block text-body-secondary small">Status</span>
                    <span class="badge order-status">
                        <?= Html::encode($statusLabels[$model->status] ?? $model->status) ?>
                    </span>
                </div>
                <div class="col-6 col-lg-3">
                    <span class="d-block text-body-secondary small">Placed</span>
                    <strong><?= Yii::$app->formatter->asDatetime($model->created_at) ?></strong>
                </div>
                <?php if (Yii::$app->user->can('order.process')) : ?>
                    <div class="col-12 col-lg-6">
                        <span class="d-block text-body-secondary small">Customer</span>
                        <strong>
                            <?= Html::encode($model->user->full_name ?: $model->user->username) ?>
                        </strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="card admin-panel order-detail-panel">
        <div class="card-header bg-transparent">
            <h2 class="h5 mb-0">Items in this order</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0 cafebara-data-table">
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
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th><?= Yii::$app->formatter->asCurrency($model->total_amount) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-body">
            <p class="small text-body-secondary mb-3">
                This is an order summary. Payment is not recorded by this system.
            </p>
            <div class="d-flex flex-wrap gap-2">
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
        </div>
    </section>
</section>
