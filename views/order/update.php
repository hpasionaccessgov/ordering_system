<?php

use app\services\Navigation;
use yii\helpers\Html;

/** @var app\models\Product[] $products */
/** @var app\models\Order $model */

$this->title = 'Update order';
$quantities = [];
foreach ($model->items as $item) {
    $quantities[$item->product_id] = (int) $item->quantity;
}
?>

<section class="order-builder-page">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">
            Order #<?= Html::encode((string) $model->id) ?>
        </p>
        <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="text-body-secondary mb-0">Adjust quantities and review the updated total before saving.</p>
    </div>

    <?= $this->render('_product_selection', [
        'products' => $products,
        'quantities' => $quantities,
        'formAction' => Navigation::url(Navigation::ORDER_UPDATE, ['id' => $model->id]),
        'submitLabel' => 'Save order changes',
    ]) ?>
</section>
