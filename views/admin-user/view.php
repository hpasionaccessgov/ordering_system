<?php

use app\models\User;
use yii\helpers\Html;

/** @var User $model */
/** @var string[] $roles */

$this->title = 'View user';
$statusLabels = [
    User::STATUS_INACTIVE => 'Inactive',
    User::STATUS_ACTIVE => 'Allowed',
    User::STATUS_DISABLED => 'Blocked',
];
?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Administration</p>
        <h1 class="h3 mb-1"><?= Html::encode($model->full_name) ?></h1>
        <p class="text-body-secondary mb-0">Account details</p>
    </div>
    <div class="d-flex gap-2">
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
        <dl class="row mb-0">
            <dt class="col-sm-3">Username</dt>
            <dd class="col-sm-9"><?= Html::encode($model->username) ?></dd>

            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9"><?= Html::encode($model->email) ?></dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                <?= Html::encode($statusLabels[(int) $model->status] ?? 'Unknown') ?>
            </dd>

            <dt class="col-sm-3">Role</dt>
            <dd class="col-sm-9">
                <?= Html::encode($roles === [] ? 'No role assigned' : implode(', ', $roles)) ?>
            </dd>

            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></dd>
        </dl>
    </div>
</section>
