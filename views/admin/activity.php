<?php

use app\models\ActivityLog;
use app\widgets\AdminGridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $event */

$this->title = 'Activity logs';
?>
<div class="mb-4">
    <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Administration</p>
    <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
</div>
<?= Html::beginForm(['/admin/activity'], 'get', ['class' => 'd-flex gap-2 mb-3', 'role' => 'search']) ?>
<?= Html::label('Event', 'activity-event', ['class' => 'visually-hidden']) ?>
<?= Html::dropDownList('event', $event, [
    '' => 'All events',
    ActivityLog::VISIT => 'Page visits',
    ActivityLog::LOGIN => 'Logins',
    ActivityLog::LOGOUT => 'Logouts',
], ['id' => 'activity-event', 'class' => 'form-select w-auto']) ?>
<?= Html::submitButton('Filter', ['class' => 'btn btn-primary']) ?>
<?= Html::endForm() ?>
<section class="card admin-panel">
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['attribute' => 'created_at', 'label' => 'Time', 'format' => 'datetime'],
            [
                'label' => 'User',
                'value' => static fn(ActivityLog $log): string => $log->user?->username ?? 'Deleted user',
            ],
            [
                'attribute' => 'event',
                'label' => 'Event',
                'value' => static fn(ActivityLog $log): string => ucfirst($log->event),
            ],
            ['attribute' => 'route', 'label' => 'Page'],
        ],
    ]) ?>
</section>