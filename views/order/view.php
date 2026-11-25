<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $model */

$this->title = $model->order_number;
$staffActionLabels = [
    Order::STATUS_PENDING => 'Confirm order',
    Order::STATUS_CONFIRMED => 'Start preparing',
    Order::STATUS_PROCESSING => 'Mark ready for pickup',
    Order::STATUS_READY_FOR_PICKUP => 'Mark completed',
];
$nextStatus = $model->nextStatus();
$canViewCustomerInformation = Yii::$app->user->can('admin.access')
    || Yii::$app->user->can('staff.access');
$priority = $model->priorityLevel();
$formatSeconds = static function (?int $seconds): string {
    if ($seconds === null) {
        return '—';
    }
    $minutes = intdiv($seconds, 60);
    $hours = intdiv($minutes, 60);
    $remMinutes = $minutes % 60;
    if ($hours > 0) {
        return $hours . 'h ' . $remMinutes . 'm';
    }
    return $minutes . 'm';
};
?>

<section class="order-detail-page">
    <div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
        <div>
            <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Order details</p>
            <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (
                !Yii::$app->user->can('admin.access')
                && !Yii::$app->user->can('staff.access')
                && Yii::$app->user->can('cart.add')
                && $model->status === Order::STATUS_COMPLETED
            ) : ?>
                <?= Html::beginForm(['/sales/orders/' . $model->id . '/order-again'], 'POST', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('Order Again', ['class' => 'btn btn-success']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <?php if (
                Yii::$app->user->can('order.view')
                && Yii::$app->user->can('order.receipt.print')
            ) : ?>
                <?= Html::a(
                    'View printable receipt',
                    ['/sales/orders/' . $model->id . '/receipt'],
                    ['class' => 'btn btn-primary'],
                ) ?>
            <?php endif; ?>
            <?php if (Yii::$app->user->can('order.index')) : ?>
                <?= Html::a(
                    'Back to orders',
                    ['/sales/orders'],
                    ['class' => 'btn btn-secondary'],
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <section class="order-detail-panel mb-4 card admin-panel">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <span class="d-block text-body-secondary small">Status</span>
                    <span class="order-status badge">
                        <?= Html::encode(Order::statusLabel((string) $model->status)) ?>
                    </span>
                    <?php if ($priority === 'warning') : ?>
                        <span class="badge text-bg-warning ms-1">Waiting 15+ min</span>
                    <?php elseif ($priority === 'critical') : ?>
                        <span class="badge text-bg-danger ms-1">Waiting 30+ min</span>
                    <?php endif; ?>
                </div>
                <div class="col-6 col-lg-3">
                    <span class="d-block text-body-secondary small">Placed</span>
                    <strong><?= Yii::$app->formatter->asDatetime($model->created_at) ?></strong>
                </div>
                <div class="col-6 col-lg-3">
                    <span class="d-block text-body-secondary small">Waiting time</span>
                    <strong><?= Html::encode($formatSeconds($model->waitingSeconds())) ?></strong>
                </div>
                <div class="col-6 col-lg-3">
                    <span class="d-block text-body-secondary small">Preparation time</span>
                    <strong><?= Html::encode($formatSeconds($model->preparationSeconds())) ?></strong>
                </div>
                <?php if ($canViewCustomerInformation) : ?>
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

    <section class="order-detail-panel mb-4 card admin-panel">
        <div class="card-header bg-transparent">
            <h2 class="mb-0 h5">Items in this order</h2>
        </div>
        <div class="table-responsive cafebara-table-scroll">
            <table class="mb-0 table table-striped table-hover table-bordered align-middle cafebara-data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit price</th>
                        <th>Subtotal</th>
                        <?php if ($canViewCustomerInformation) : ?>
                            <th>Created by</th>
                            <th>Updated by</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->items as $item) : ?>
                        <tr>
                            <td><?= Html::encode($item->product_name) ?></td>
                            <td><?= Html::encode((string) $item->quantity) ?></td>
                            <td><?= Yii::$app->formatter->asCurrency($item->unit_price) ?></td>
                            <td>
                                <?= Yii::$app->formatter->asCurrency(
                                    $item->subtotal,
                                ) ?>
                            </td>
                            <?php if ($canViewCustomerInformation) : ?>
                                <td><?= Html::encode($item->createdBy?->username ?? 'System') ?></td>
                                <td><?= Html::encode($item->updatedBy?->username ?? 'System') ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="<?= $canViewCustomerInformation ? 5 : 3 ?>" class="text-end">Total</th>
                        <th><?= Yii::$app->formatter->asCurrency($model->total_amount) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-body">
            <p class="mb-3 text-body-secondary small">
                This is an order summary. Payment is not recorded by this system.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <?= $this->render('_cancel', ['model' => $model]) ?>

                <?php if (Yii::$app->user->can('order.process') && $nextStatus !== null) : ?>
                    <?= Html::beginForm(
                        ['/sales/orders/' . $model->id . '/process'],
                        'POST',
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

    <section class="card admin-panel">
        <div class="card-header bg-transparent">
            <h2 class="mb-0 h5">Status history</h2>
        </div>
        <?php if ($model->statusHistory === []) : ?>
            <div class="card-body text-body-secondary">No status changes have been recorded.</div>
        <?php else : ?>
            <div class="list-group list-group-flush">
                <?php foreach ($model->statusHistory as $history) : ?>
                    <div class="d-flex flex-wrap gap-2 list-group-item justify-content-between">
                        <div>
                            <strong>
                                <?= Html::encode(Order::statusLabel((string) $history->to_status)) ?>
                            </strong>
                            <?php if ($history->from_status !== null) : ?>
                                <span class="text-body-secondary">
                                    from <?= Html::encode(Order::statusLabel((string) $history->from_status)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="text-end text-body-secondary small">
                            <div><?= Yii::$app->formatter->asDatetime($history->created_at) ?></div>
                            <?php if (Yii::$app->user->can('order.process')) : ?>
                                <div>
                                    <?= Html::encode(
                                        $history->changedBy?->full_name
                                            ?: $history->changedBy?->username
                                            ?: 'System migration',
                                    ) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>
