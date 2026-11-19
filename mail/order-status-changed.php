<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $order */

$labels = [
    Order::STATUS_PENDING => 'Pending',
    Order::STATUS_CONFIRMED => 'Confirmed',
    Order::STATUS_PROCESSING => 'Processing',
    Order::STATUS_COMPLETED => 'Completed',
    Order::STATUS_CANCELLED => 'Cancelled',
];
?>
<h1>Order update</h1>
<p>Your Cafébara order <strong><?= Html::encode($order->order_number) ?></strong> is now
    <strong><?= Html::encode($labels[$order->status] ?? $order->status) ?></strong>.</p>
<p>Total: <?= Html::encode(Yii::$app->formatter->asCurrency($order->total_amount)) ?></p>
<p>Sign in to Cafébara to review the order details and status history.</p>
