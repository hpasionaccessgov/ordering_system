<?php

use app\models\OrderNotification;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Notifications';
?>
<h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>
<div class="card">
    <div class="list-group list-group-flush">
        <?php foreach ($dataProvider->getModels() as $notification): ?>
            <?php /** @var OrderNotification $notification */ ?>
            <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <?php if ($notification->read_at === null): ?>
                        <span class="badge text-bg-primary">Unread</span>
                    <?php endif ?>
                    <span>
                        <?= Html::encode($notification->status === \app\models\Order::STATUS_PENDING
                            ? 'New order ' . $notification->order->order_number . ' received.'
                            : 'Order ' . $notification->order->order_number . ' is ' . $notification->status . '.') ?>
                    </span>
                    <div class="small text-body-secondary">
                        <?= Html::encode(Yii::$app->formatter->asDatetime($notification->created_at)) ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?php if (Yii::$app->user->can('order.view')): ?>
                        <?= Html::a('View order', ['/order/view', 'id' => $notification->order_id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?php endif ?>
                    <?php if ($notification->read_at === null): ?>
                        <?= Html::beginForm(['/notification/read', 'id' => $notification->id], 'post') ?>
                        <?= Html::submitButton('Mark as read', ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= Html::endForm() ?>
                    <?php endif ?>
                </div>
            </div>
        <?php endforeach ?>
        <?php if ($dataProvider->getCount() === 0): ?>
            <div class="list-group-item text-body-secondary">No notifications yet.</div>
        <?php endif ?>
    </div>
</div>
<?= LinkPager::widget(['pagination' => $dataProvider->getPagination()]) ?>
