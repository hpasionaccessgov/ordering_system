<?php

use app\models\ActivityLog;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var ActivityLog $model */

$this->title = 'Activity log #' . $model->id;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->user->can('activityLog.index')) : ?>
        <?= Html::a('Back to activity logs', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php endif; ?>
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
