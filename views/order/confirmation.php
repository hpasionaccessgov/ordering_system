<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $model */

$this->title = 'Order confirmation';
?>

<section class="order-confirmation-page mx-auto">
    <div class="mb-4 card text-center admin-panel">
        <div class="py-5 card-body">
            <i class="text-success fs-1 bi bi-check-circle" aria-hidden="true"></i>
            <h1 class="mt-3 h3">Thank you. Your order is placed.</h1>
            <p class="mb-2 text-body-secondary">
                Order <?= Html::encode($model->order_number) ?> is pending confirmation.
            </p>
            <strong class="fs-4"><?= Yii::$app->formatter->asCurrency($model->total_amount) ?></strong>
            <div class="d-flex flex-wrap gap-2 mt-4 justify-content-center">
                <?= Html::a('View order', ['/sales/orders/' . $model->id], [
                    'class' => 'btn btn-primary',
                ]) ?>
                <?php if (Yii::$app->user->can('order.index')) : ?>
                    <?= Html::a('Order history', ['/sales/orders'], [
                        'class' => 'btn btn-secondary',
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
                    <li class="d-flex gap-3 px-0 list-group-item justify-content-between">
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
