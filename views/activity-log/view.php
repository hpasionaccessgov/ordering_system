<?php

use app\models\ActivityLog;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var ActivityLog $model */

$this->title = 'Activity log #' . $model->id;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-start">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Administration</p>
        <h1 class="mb-1 h3"><?= Html::encode($this->title) ?></h1>
        <p class="mb-0 text-body-secondary">Activity log details</p>
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
