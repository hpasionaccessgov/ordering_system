<?php

use app\services\Navigation;
use app\models\Order;
use app\models\Product;
use yii\helpers\Html;

/** @var Product $model */
/** @var list<Order> $eligibleOrders */

$orderOptions = [];
foreach ($eligibleOrders as $order) {
    $orderOptions[(int) $order->id] = $order->order_number
        . ' — ' . Yii::$app->formatter->asDate($order->created_at)
        . ' (' . ucfirst((string) $order->status) . ')';
}
?>
<div class="mt-4 pt-4 border-top product-review-form">
    <h3 class="h6 mb-1">Share your experience</h3>
    <p class="mb-3 text-body-secondary small">
        Reviews can only be added from completed orders that include this product.
    </p>
    <?= Html::beginForm(Navigation::url(Navigation::REVIEW_CREATE, ['id' => $model->id]), 'POST', ['class' => 'row g-3']) ?>
    <div class="col-12 col-md-6">
        <?= Html::label('Completed order', 'review-order-id', ['class' => 'form-label small fw-semibold']) ?>
        <?= Html::dropDownList('order_id', null, $orderOptions, [
            'id' => 'review-order-id',
            'class' => 'form-select',
            'prompt' => 'Select an order',
            'required' => true,
        ]) ?>
    </div>
    <div class="col-12 col-md-6">
        <?= Html::label('Rating', 'review-rating', ['class' => 'form-label small fw-semibold']) ?>
        <?= Html::dropDownList('rating', null, [
            5 => '5 stars',
            4 => '4 stars',
            3 => '3 stars',
            2 => '2 stars',
            1 => '1 star',
        ], [
            'id' => 'review-rating',
            'class' => 'form-select',
            'prompt' => 'Select a rating',
            'required' => true,
        ]) ?>
    </div>
    <div class="col-12">
        <?= Html::label('Your review', 'review-text', ['class' => 'form-label small fw-semibold']) ?>
        <?= Html::textarea('review', '', [
            'id' => 'review-text',
            'class' => 'form-control',
            'rows' => 3,
            'maxlength' => 1000,
            'placeholder' => 'What did you like?',
            'required' => true,
        ]) ?>
    </div>
    <div class="col-12">
        <?= Html::submitButton('Post review', ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm() ?>
</div>
