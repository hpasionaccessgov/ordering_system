<?php

use app\models\ProfileForm;
use app\models\User;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var User $user */
/** @var ProfileForm $model */

$this->title = 'My Profile';
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0 card-title">Profile details</h2>
            </div>

            <div class="card-body">
                <dl class="row mb-4">

                    <dt class="col-sm-3">Full Name</dt>
                    <dd class="col-sm-9"><?= Html::encode($user->full_name) ?></dd>

                    <dt class="col-sm-3">Username</dt>
                    <dd class="col-sm-9"><?= Html::encode($user->username) ?></dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9"><?= Html::encode($user->email) ?></dd>

                </dl>

                <?php $form = AdminActiveForm::begin(['id' => 'profile-form']); ?>

                <?= $form->field($model, 'fullName')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>


                <?= Html::submitButton('Save profile', ['class' => 'btn btn-primary']) ?>

                <?php AdminActiveForm::end(); ?>
            </div>
        </div>

        <div class="mt-4 card">
            <div class="card-header">
                <h2 class="mb-0 card-title">Account actions</h2>
            </div>

            <div class="d-flex flex-wrap gap-2 card-body">
                <?= Html::a('Change password', ['/profile/change-password'], ['class' => 'btn btn-primary']) ?>

                <?= Html::a('Deactivate account', ['/profile/deactivate'], ['class' => 'btn btn-danger']) ?>

                <?= Html::beginForm(['/site/logout'], 'POST', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('Logout', ['class' => 'btn btn-warning']) ?>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>