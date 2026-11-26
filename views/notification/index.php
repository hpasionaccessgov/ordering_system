<?php

use app\services\Navigation;
use app\models\Notification;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */
/** @var string $status */
/** @var string $read */
/** @var string $category */
/** @var string $sort */
/** @var int $unreadCount */

$this->title = 'Notifications';
?>
<div class="d-flex flex-wrap gap-2 mb-4 justify-content-between align-items-center">
    <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->user->can('notification.read')) : ?>
        <?= Html::beginForm(Navigation::url(Navigation::NOTIFICATION_READ_ALL), 'POST', ['class' => 'm-0']) ?>
        <?= Html::submitButton('Mark all as read', [
            'class' => 'btn btn-primary btn-sm',
            'disabled' => $unreadCount === 0,
        ]) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
</div>
<?= $this->render('_search', [
    'search' => $search,
    'status' => $status,
    'read' => $read,
    'category' => $category,
    'sort' => $sort,
]) ?>
<div class="card admin-panel">
    <div class="list-group list-group-flush">
        <?php foreach ($dataProvider->getModels() as $notification): ?>
            <?php /** @var Notification $notification */ ?>
            <?php $isUnread = $notification->read_at === null; ?>
            <div class="d-flex flex-wrap gap-3 list-group-item justify-content-between align-items-start border-start border-primary border-3 <?= $isUnread ? 'bg-primary-subtle' : '' ?>">
                <div class="d-flex gap-3 align-items-start">
                    <span class="fs-5 text-body-secondary" aria-hidden="true"><i class="bi <?= Html::encode($notification->categoryIconClass) ?>"></i></span>
                    <div>
                        <div class="d-flex flex-wrap gap-2 mb-1 align-items-center">
                            <span class="badge <?= Html::encode($notification->categoryBadgeClass) ?>"><?= Html::encode($notification->categoryLabel) ?></span>
                            <?php if ($isUnread): ?>
                                <span class="badge text-bg-primary">Unread</span>
                            <?php endif; ?>
                        </div>
                        <div class="<?= $isUnread ? 'fw-semibold' : '' ?>"><?= Html::encode($notification->label) ?></div>
                        <div class="text-body-secondary small">
                            <?= Html::encode(Yii::$app->formatter->asDatetime($notification->created_at)) ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($isUnread && Yii::$app->user->can('notification.read')): ?>
                        <?= Html::beginForm(Navigation::url(Navigation::NOTIFICATION_READ, ['id' => $notification->id]), 'POST', ['class' => 'm-0']) ?>
                        <?= Html::submitButton('Mark as read', ['class' => 'btn btn-sm btn-primary']) ?>
                        <?= Html::endForm() ?>
                    <?php endif ?>
                    <?php if (Yii::$app->user->can('notification.read')): ?>
                        <?= Html::a('View', Navigation::url(Navigation::NOTIFICATION_READ, ['id' => $notification->id]), ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                    <?php elseif (Yii::$app->user->can('order.view') && $notification->order_id !== null): ?>
                        <?= Html::a('View order', Navigation::url(Navigation::ORDER_VIEW, ['id' => $notification->order_id]), ['class' => 'btn btn-sm btn-primary']) ?>
                    <?php endif ?>
                    <?php if (Yii::$app->user->can('notification.delete')): ?>
                        <?= Html::beginForm(Navigation::url(Navigation::NOTIFICATION_DELETE, ['id' => $notification->id]), 'POST', ['class' => 'm-0']) ?>
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
            <div class="py-4 list-group-item text-body-secondary text-center">No notifications found.</div>
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