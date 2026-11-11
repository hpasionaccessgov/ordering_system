<?php

use app\models\ChangePasswordForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var app\models\ChangePasswordForm $model */

$this->title = 'Change password';
?>

<h1> <?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'currentPassword')->passwordInput() ?>
<?= $form->field($model, 'newPassword')->passwordInput() ?>
<?= $form->field($model, 'confirmPassword')->passwordInput() ?>

<?= Html::submitButton('Change password', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end(); ?>