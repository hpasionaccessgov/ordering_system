<?php

use app\models\ProductReview;
use yii\helpers\Html;

/** @var ProductReview $review */

$customer = $review->user?->full_name ?: $review->user?->username ?: 'Customer';
?>
<div class="border-bottom py-3 product-review-item">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <strong class="small"><?= Html::encode($customer) ?></strong>
        <span class="text-warning small" aria-label="<?= (int) $review->rating ?> out of 5 stars">
            <?= str_repeat('★', (int) $review->rating) ?><span class="text-body-secondary"><?= str_repeat('★', 5 - (int) $review->rating) ?></span>
        </span>
    </div>
    <p class="mb-1 mt-2"><?= nl2br(Html::encode((string) $review->review)) ?></p>
    <span class="text-body-secondary small">
        <?= Yii::$app->formatter->asDatetime($review->created_at) ?>
        · Order <?= Html::encode($review->order?->order_number ?? '—') ?>
    </span>
</div>
