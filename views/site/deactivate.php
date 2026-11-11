<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var app\models\ChangePasswordForm $model */

$this->title = 'Deactivate account';
?>

<h1><?= Html::encode($this->title) ?></h1>
<p> Deactivate your account will sign you out and prevent future logins.</p>

<?php $form = ActiveForm::begin(['method' => 'post']); ?>

<?= $form->errorSummary($model) ?>

<?= $form->field($model, 'currentPassword')->passwordInput()->label('Confirm your current password') ?>

<?= Html::submitButton('Deactivate my account', ['class' => 'btn btn-danger',]) ?>

<?php ActiveForm::end(); ?>