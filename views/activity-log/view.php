<?php

use app\models\ActivityLog;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var ActivityLog $model */

$this->title = 'Activity log #' . $model->id;
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Administration</p>
        <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="text-body-secondary mb-0">Activity log details</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (Yii::$app->user->can('activityLog.index')) : ?>
            <?= Html::a('Back to activity logs', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?php endif; ?>
    </div>
</div>
<section class="card admin-panel">
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                ['label' => 'User', 'value' => $model->user?->username ?? 'Deleted user'],
                'user_id',
                ['attribute' => 'event', 'value' => ucfirst($model->event)],
                ['attribute' => 'route', 'label' => 'Page'],
                ['attribute' => 'created_at', 'label' => 'Time', 'format' => 'datetime'],
            ],
        ]) ?>
    </div>
</section>
