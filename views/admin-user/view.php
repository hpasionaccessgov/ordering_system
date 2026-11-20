<?php

use app\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var User $model */
/** @var string[] $roles */

$this->title = $model->full_name;
$statusLabels = [
    User::STATUS_INACTIVE => 'Inactive',
    User::STATUS_ACTIVE => 'Allowed',
    User::STATUS_DISABLED => 'Blocked',
];
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Administration</p>
        <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="text-body-secondary mb-0">Account details</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (
            Yii::$app->user->can('user.update')
            && Yii::$app->user->can('user.view')
        ) : ?>
            <?= Html::a('Edit account', ['/admin-user/update', 'id' => $model->id], [
                'class' => 'btn btn-primary',
            ]) ?>
        <?php endif; ?>
        <?php if (Yii::$app->user->can('user.index')) : ?>
            <?= Html::a('Back to user list', ['/admin-user/list'], [
                'class' => 'btn btn-outline-secondary',
            ]) ?>
        <?php endif; ?>
    </div>
</div>

<section class="card admin-panel">
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'username',
                'email',
                ['attribute' => 'status', 'value' => $statusLabels[(int) $model->status] ?? 'Unknown'],
                ['label' => 'Role', 'value' => $roles === [] ? 'No role assigned' : implode(', ', $roles)],
                ['attribute' => 'created_at', 'format' => 'datetime'],
            ],
        ]) ?>
    </div>
</section>
