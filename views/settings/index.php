<?php

use app\models\ApplicationSettings;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var ApplicationSettings $model */

$this->title = 'Settings';
$canUpdate = Yii::$app->user->can('settings.update');
?>
<div class="mb-4">
    <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Administration</p>
    <h1 class="h3 mb-0">Settings</h1>
</div>
<section class="card admin-panel">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['action' => ['update'], 'method' => 'post']); ?>
        <h2 class="h5">Pagination</h2>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'card_page_size')->input('number', ['min' => 1, 'max' => 100, 'readonly' => !$canUpdate]) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'table_page_size')->input('number', ['min' => 1, 'max' => 100, 'readonly' => !$canUpdate]) ?></div>
        </div>
        <p class="text-body-secondary small">Cards cover products and featured products. Tables and lists include notifications and activity logs.</p>
        <h2 class="h5 mt-3">Security</h2>
        <div class="row">
            <?php foreach (['idle_minutes', 'login_max_attempts', 'login_reset_minutes', 'login_lock_minutes'] as $field): ?>
                <div class="col-md-6"><?= $form->field($model, $field)->input('number', ['min' => 1, 'max' => $field === 'login_max_attempts' ? 100 : 1440, 'readonly' => !$canUpdate]) ?></div>
            <?php endforeach ?>
        </div>
        <p class="text-body-secondary small">Inactive users must sign in again, even when Remember me is selected.</p>
        <?php if ($canUpdate): ?>
            <?= Html::submitButton('Save settings', ['class' => 'btn btn-primary']) ?>
        <?php endif ?>
        <?php ActiveForm::end(); ?>
    </div>
</section>
