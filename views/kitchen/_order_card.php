<?php

declare(strict_types=1);

use app\models\Order;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var Order $order */

$now = time();
$age = $order->ageMinutes($now);
$priority = $order->priorityLevel($now);
$priorityClass = match ($priority) {
    'critical' => 'border-danger border-2',
    'warning' => 'border-warning border-2',
    default => '',
};
$priorityBadge = match ($priority) {
    'critical' => '<span class="badge text-bg-danger">Critical</span>',
    'warning' => '<span class="badge text-bg-warning">Warning</span>',
    default => '',
};
$nextStatus = $order->nextStatus();
$actionLabels = [
    Order::STATUS_PENDING => 'Confirm order',
    Order::STATUS_CONFIRMED => 'Start preparing',
    Order::STATUS_PROCESSING => 'Mark ready',
    Order::STATUS_READY_FOR_PICKUP => 'Mark completed',
];
$returnUrl = Url::to(['/sales/kitchen-board']);
?>
<article class="mb-3 card shadow-sm <?= Html::encode($priorityClass) ?>">
    <div class="p-3 card-body">
        <div class="d-flex gap-2 mb-2 justify-content-between align-items-start">
            <div>
                <div class="fw-semibold"><?= Html::encode($order->order_number) ?></div>
                <div class="text-body-secondary small"><?= Html::encode($order->user->full_name ?: $order->user->username) ?></div>
            </div>
            <div class="text-end">
                <div class="text-body-secondary small"><?= Html::encode((string) $age) ?> min</div>
                <?= $priorityBadge ?>
            </div>
        </div>

        <ul class="mb-2 list-unstyled small">
            <?php foreach ($order->items as $item) : ?>
                <li><?= Html::encode((string) $item->quantity) ?> × <?= Html::encode($item->product_name) ?></li>
            <?php endforeach; ?>
        </ul>

        <div class="d-flex mb-3 justify-content-between align-items-center text-body-secondary small">
            <span><?= Yii::$app->formatter->asCurrency($order->total_amount) ?></span>
            <span><?= Yii::$app->formatter->asDatetime($order->created_at) ?></span>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if (Yii::$app->user->can('order.view')) : ?>
                <?= Html::a('View', ['/sales/orders/' . $order->id], ['class' => 'btn btn-sm btn-secondary']) ?>
            <?php endif; ?>
            <?php if (Yii::$app->user->can('order.process') && $nextStatus !== null) : ?>
                <?= Html::beginForm(['/sales/orders/' . $order->id . '/process'], 'post', ['class' => 'd-inline']) ?>
                <?= Html::hiddenInput('returnUrl', $returnUrl) ?>
                <?= Html::submitButton($actionLabels[(string) $order->status] ?? 'Advance', ['class' => 'btn btn-sm btn-primary']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
            <?= $this->render('/order/_cancel', [
                'model' => $order,
                'returnUrl' => $returnUrl,
                'buttonClass' => 'btn btn-sm btn-danger',
                'buttonLabel' => 'Cancel',
            ]) ?>

        </div>
    </div>
</article>