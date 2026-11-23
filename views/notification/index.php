<?php

use app\models\Notification;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */
/** @var string $status */
/** @var string $read */

$this->title = 'Notifications';
?>
<h1 class="mb-4 h3"><?= Html::encode($this->title) ?></h1>
<?= $this->render('_search', [
    'search' => $search,
    'status' => $status,
    'read' => $read,
]) ?>
<div class="card admin-panel">
    <div class="list-group list-group-flush">
        <?php foreach ($dataProvider->getModels() as $notification): ?>
            <?php /** @var Notification $notification */ ?>
            <div class="d-flex flex-wrap gap-2 list-group-item justify-content-between align-items-center">
                <div>
                    <?php if ($notification->read_at === null): ?>
                        <span class="me-2 badge text-bg-primary ">Unread</span>
                    <?php endif ?>
                    <span><?= Html::encode($notification->label) ?></span>
                    <div class="text-body-secondary small">
                        <?= Html::encode(Yii::$app->formatter->asDatetime($notification->created_at)) ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?php if (Yii::$app->user->can('notification.read')): ?>
                        <?= Html::a('View', ['/notification/view', 'id' => $notification->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?php elseif (Yii::$app->user->can('order.view') && $notification->order_id !== null): ?>
                        <?= Html::a('View order', ['/order/view', 'id' => $notification->order_id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    <?php endif ?>
                    <?php if ($notification->read_at === null && Yii::$app->user->can('notification.read')): ?>
                        <?= Html::beginForm(['/notification/read', 'id' => $notification->id], 'post') ?>
                        <?= Html::submitButton('Mark as read', ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= Html::endForm() ?>
                    <?php endif ?>
                    <?php if (Yii::$app->user->can('notification.delete')): ?>
                        <?= Html::beginForm(['/notification/delete', 'id' => $notification->id], 'post') ?>
                        <?= Html::submitButton('Delete', [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data-confirm-modal' => 'Delete this notification? This cannot be undone.',
                        ]) ?>
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
<div class="d-flex mt-4 justify-content-center">
    <?= LinkPager::widget([
        'pagination' => $dataProvider->getPagination(),
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
    ]) ?>
</div>