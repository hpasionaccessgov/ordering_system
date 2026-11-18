<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $model */

$this->title = 'Order confirmation';
?>

<section class="order-confirmation-page mx-auto">
    <div class="card admin-panel text-center mb-4">
        <div class="card-body py-5">
            <i class="bi bi-check-circle text-success fs-1" aria-hidden="true"></i>
            <h1 class="h3 mt-3">Thank you. Your order is placed.</h1>
            <p class="text-body-secondary mb-2">
                Order <?= Html::encode($model->order_number) ?> is pending confirmation.
            </p>
            <strong class="fs-4"><?= Yii::$app->formatter->asCurrency($model->total_amount) ?></strong>
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                <?= Html::a('View order', ['/order/view', 'id' => $model->id], [
                    'class' => 'btn btn-primary',
                ]) ?>
                <?php if (Yii::$app->user->can('order.index')) : ?>
                    <?= Html::a('Order history', ['/order/index'], [
                        'class' => 'btn btn-outline-secondary',
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <section class="card admin-panel">
        <div class="card-body">
            <h2 class="h5">Items</h2>
            <ul class="list-group list-group-flush">
                <?php foreach ($model->items as $item): ?>
                    <li class="list-group-item d-flex justify-content-between gap-3 px-0">
                        <span>
                            <?= Html::encode($item->product_name) ?>
                            <span class="text-body-secondary">× <?= Html::encode((string) $item->quantity) ?></span>
                        </span>
                        <strong><?= Yii::$app->formatter->asCurrency($item->subtotal) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</section>
