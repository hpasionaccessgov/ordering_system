<?php

use app\models\ActivityLog;
use app\widgets\AdminGridView;
use yii\helpers\Html;

/** @var app\models\ActivityLogSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Activity logs';
$sort = Yii::$app->request->get('sort');
$value = static fn($value): string => is_scalar($value) ? (string) $value : '';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Administration</p>
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
    </div>
</div>
<?= Html::beginForm(['/activity-log/index'], 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md-2">
        <?= Html::input('text', 'ActivityLogSearch[id]', $value($searchModel->id), ['class' => 'form-control', 'placeholder' => 'Log ID', 'aria-label' => 'Log ID']) ?>
    </div>
    <div class="col-12 col-md-2">
        <?= Html::input('text', 'ActivityLogSearch[user_id]', $value($searchModel->user_id), ['class' => 'form-control', 'placeholder' => 'User ID', 'aria-label' => 'User ID']) ?>
    </div>
    <div class="col-12 col-md">
        <?= Html::input('text', 'ActivityLogSearch[username]', $value($searchModel->username), ['class' => 'form-control', 'placeholder' => 'Username', 'aria-label' => 'Username']) ?>
    </div>
    <div class="col-12 col-md">
        <?= Html::input('text', 'ActivityLogSearch[route]', $value($searchModel->route), ['class' => 'form-control', 'placeholder' => 'Page', 'aria-label' => 'Page']) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('event', $value($searchModel->event), [
            '' => 'All events', ActivityLog::VISIT => 'Page visits',
            ActivityLog::LOGIN => 'Logins', ActivityLog::LOGOUT => 'Logouts',
        ], ['class' => 'form-select', 'aria-label' => 'Event']) ?>
    </div>
    <?php if (is_string($sort) && $sort !== '') : ?>
        <?= Html::hiddenInput('sort', $sort) ?>
    <?php endif; ?>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', ['index'], ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<div class="row g-2 mt-1">
    <div class="col-12 col-md-auto">
        <?= Html::label('From', 'activity-created-from', ['class' => 'form-label']) ?>
        <?= Html::input('date', 'created_from', $value($searchModel->filters->created_from), ['id' => 'activity-created-from', 'class' => 'form-control']) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::label('To', 'activity-created-to', ['class' => 'form-label']) ?>
        <?= Html::input('date', 'created_to', $value($searchModel->filters->created_to), ['id' => 'activity-created-to', 'class' => 'form-control']) ?>
    </div>
</div>
<?= Html::errorSummary([$searchModel, $searchModel->filters], ['class' => 'alert alert-danger mt-2']) ?>
<?= Html::endForm() ?>
<section class="card admin-panel">
    <div class="card-header">
        <p class="text-body-secondary mb-0">Review sign-in, sign-out, and page visit activity.</p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            ['attribute' => 'created_at', 'label' => 'Time', 'format' => 'datetime'],
            [
                'attribute' => 'username',
                'label' => 'User',
                'value' => static fn(ActivityLog $log): string => $log->user?->username ?? 'Deleted user',
            ],
            ['attribute' => 'event', 'value' => static fn(ActivityLog $log): string => ucfirst($log->event)],
            ['attribute' => 'route', 'label' => 'Page'],
            [
                'label' => 'Actions',
                'format' => 'raw',
                'visible' => Yii::$app->user->can('activityLog.view'),
                'value' => static fn(ActivityLog $log): string => Html::a(
                    'View', ['view', 'id' => $log->id], ['class' => 'btn btn-sm btn-outline-primary'],
                ),
            ],
        ],
    ]) ?>
</section>
