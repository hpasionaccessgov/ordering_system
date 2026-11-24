<?php

use app\models\ApplicationSettings;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var ApplicationSettings $model */

$this->title = 'Settings';
$canUpdate = Yii::$app->user->can('settings.update');
?>
<div class="mb-4">
    <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Administration</p>
    <h1 class="mb-0 h3">Settings</h1>
</div>
<section class="card admin-panel">
    <div class="card-body">
        <?php $form = ActiveForm::begin(['action' => ['update'], 'method' => 'POST']); ?>
        <h2 class="h5">Pagination</h2>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'card_page_size')->input('number', ['min' => 1, 'max' => 100, 'readonly' => !$canUpdate]) ?></div>
            <div class="col-md-6"><?= $form->field($model, 'table_page_size')->input('number', ['min' => 1, 'max' => 100, 'readonly' => !$canUpdate]) ?></div>
        </div>
        <p class="text-body-secondary small">Cards cover products and featured products. Tables and lists include notifications and activity logs.</p>
        <h2 class="mt-3 h5">Security</h2>
        <div class="row">
            <?php foreach (['idle_minutes', 'login_max_attempts', 'login_reset_minutes', 'login_lock_minutes', 'logs_expiration_days'] as $field): ?>
                <?php
                $options = ['min' => 1, 'max' => $field === 'login_max_attempts' ? 100 : ($field === 'logs_expiration_days' ? 3650 : 1440), 'readonly' => !$canUpdate];
                if ($field === 'logs_expiration_days' && is_array($model->$field)) {
                    $options['value'] = '';
                }
                ?>
                <div class="col-md-6"><?= $form->field($model, $field)->input('number', $options) ?></div>
            <?php endforeach ?>
        </div>
        <p class="text-body-secondary small">Inactive users must sign in again, even when Remember me is selected.</p>
        <p class="text-body-secondary small">Expired activity logs are permanently deleted during app use, checked daily. Changing the retention period can trigger an earlier cleanup. Order history is not deleted.</p>
        <h2 class="mt-3 h5">Notifications</h2>
        <div class="row">
            <div class="col-md-6"><?= $form->field($model, 'notifications_expiration_days')->input('number', [
                'min' => 1,
                'max' => 3650,
                'readonly' => !$canUpdate,
                'value' => is_array($model->notifications_expiration_days) ? '' : $model->notifications_expiration_days,
            ]) ?></div>
        </div>
        <p class="text-body-secondary small">Notifications older than this are permanently deleted during app use, checked daily. Order history is not deleted.</p>
        <?php if ($canUpdate): ?>
            <?= Html::submitButton('Save settings', ['class' => 'btn btn-primary']) ?>
        <?php endif ?>
        <?php ActiveForm::end(); ?>
    </div>
</section>
