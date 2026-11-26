<?php

declare(strict_types=1);

use app\models\Order;
use app\services\Navigation;
use app\services\OrderAccessPolicy;
use yii\helpers\Html;

/** @var Order $order */

$now = time();
$elapsed = $order->queueElapsedSeconds($now);
$priority = $order->priorityLevel($now);
$priorityBadge = match ($priority) {
    'critical' => 'Critical',
    'warning' => 'Warning',
    default => '',
};
$tone = match ((string) $order->status) {
    Order::STATUS_CONFIRMED => 'confirmed',
    Order::STATUS_PROCESSING => 'processing',
    Order::STATUS_READY_FOR_PICKUP => 'ready',
    default => 'pending',
};
$nextStatus = $order->nextStatus();
$actionLabels = [
    Order::STATUS_PENDING => 'Confirm order',
    Order::STATUS_CONFIRMED => 'Start preparing',
    Order::STATUS_PROCESSING => 'Mark ready',
    Order::STATUS_READY_FOR_PICKUP => 'Mark completed',
];
$returnUrl = \yii\helpers\Url::to(Navigation::url(Navigation::KITCHEN_BOARD));
$canView = Yii::$app->user->can('order.view') && (
    OrderAccessPolicy::canViewAllOrders(
        Yii::$app->user->can('admin.access'),
        Yii::$app->user->can('staff.access'),
    )
    || (int) $order->user_id === (int) Yii::$app->user->id
);
$elapsedLabel = $elapsed === null
    ? '--:--'
    : sprintf('%02d:%02d', intdiv($elapsed, 60), $elapsed % 60);
$confirmedAt = $order->hasAttribute('confirmed_at') && $order->confirmed_at !== null
    ? (int) $order->confirmed_at
    : null;
?>
<article class="kitchen-ticket" data-tone="<?= Html::encode($tone) ?>" data-priority="<?= Html::encode($priority) ?>" data-confirmed-at="<?= $confirmedAt ?? '' ?>">
    <div class="kitchen-ticket-head">
        <h3 class="kitchen-ticket-number">#<?= Html::encode($order->order_number) ?></h3>
        <p class="mb-0 kitchen-ticket-timer" data-level="<?= Html::encode($priority) ?>">
            <i class="bi bi-stopwatch" aria-hidden="true"></i>
            <span class="visually-hidden">Elapsed since confirmation: </span>
            <span data-timer-value><?= Html::encode($elapsedLabel) ?></span>
        </p>
    </div>

    <div class="kitchen-ticket-meta">
        <p class="mb-0 kitchen-ticket-customer"><?= Html::encode($order->user->full_name ?: $order->user->username) ?></p>
        <span class="kitchen-ticket-flag" data-level="<?= Html::encode($priority) ?>"<?= $priorityBadge === '' ? ' hidden' : '' ?>><?= Html::encode($priorityBadge) ?></span>
    </div>

    <ul class="list-unstyled kitchen-ticket-items">
        <?php foreach ($order->items as $item) : ?>
            <li class="kitchen-ticket-item">
                <span class="kitchen-ticket-qty"><?= Html::encode((string) $item->quantity) ?>&times;</span>
                <span class="kitchen-ticket-item-name"><?= Html::encode($item->product_name) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <footer class="kitchen-ticket-foot">
        <span class="kitchen-ticket-total"><?= Yii::$app->formatter->asCurrency($order->total_amount) ?></span>
        <time datetime="<?= Html::encode(date(DATE_ATOM, (int) $order->created_at)) ?>">
            <?= Yii::$app->formatter->asDatetime($order->created_at) ?>
        </time>
    </footer>

    <div class="kitchen-ticket-actions">
        <?php if ($canView) : ?>
            <?= Html::a('View order', Navigation::url(Navigation::ORDER_VIEW, ['id' => $order->id]), ['class' => 'btn btn-sm btn-outline-primary']) ?>
        <?php endif; ?>
        <?php if (Yii::$app->user->can('order.process') && $nextStatus !== null) : ?>
            <?= Html::beginForm(Navigation::url(Navigation::ORDER_PROCESS, ['id' => $order->id]), 'post', ['class' => 'd-inline']) ?>
            <?= Html::hiddenInput('returnUrl', $returnUrl) ?>
            <?= Html::submitButton($actionLabels[(string) $order->status] ?? 'Advance', ['class' => 'btn btn-sm btn-primary']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
        <?= $this->render('/order/_cancel', [
            'model' => $order,
            'returnUrl' => $returnUrl,
            'buttonClass' => 'btn btn-sm btn-outline-danger',
            'buttonLabel' => 'Cancel order',
        ]) ?>
    </div>
</article>
