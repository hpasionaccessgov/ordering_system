<?php

use app\models\ProfileForm;
use app\models\User;
use app\widgets\AdminActiveForm;
use app\widgets\AdminButton;
use yii\helpers\Html;

/** @var User $user */
/** @var ProfileForm $model */

$this->title = 'My Profile';
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title mb-0">Profile details</h2>
            </div>

            <div class="card-body">
                <dl class="row mb-4">
                    <dt class="col-sm-3">Username</dt>
                    <dd class="col-sm-9"><?= Html::encode($user->username) ?></dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9"><?= Html::encode($user->email) ?></dd>
                </dl>

                <?php $form = AdminActiveForm::begin(); ?>

                <?= $form->field($model, 'fullName')->textInput(['maxlength' => true]) ?>

                <?= AdminButton::submit('Save profile') ?>

                <?php AdminActiveForm::end(); ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h2 class="card-title mb-0">Account actions</h2>
            </div>

            <div class="card-body d-flex flex-wrap gap-2">
                <?= AdminButton::link('Change password', ['/site/change-password'], 'outline-primary') ?>

                <?= AdminButton::link('Deactivate account', ['/site/deactivate'], 'outline-danger') ?>

                <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline']) ?>
                <?= AdminButton::submit('Logout', 'outline-warning') ?>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>