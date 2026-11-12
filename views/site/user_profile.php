<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var app\models\User $user */
/** @var app\models\ProfileForm $model */

$this->title = 'My Profile';
?>

<h1><?= Html::encode($this->title) ?></h1>

<p>Username: <?= Html::encode($user->username) ?></p>
<p>Email: <?= Html::encode($user->email) ?></p>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'fullName')->textInput(['maxlength' => true]) ?>

<?= Html::submitButton('Save profile', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end(); ?>

<p class="mt-3">
    <?= Html::a('Change password', ['site/change-password']) ?>
</p>
<p>
    <?= Html::a('Deactivate account', ['site/deactivate'], ['class' => 'text-danger']) ?>
</p>

<p class="mt-3">
    <?= Html::a('Logout', ['/site/logout'], [
        'data-method' => 'post',
        'class' => 'text-warning logout',
    ]) ?>
</p>
