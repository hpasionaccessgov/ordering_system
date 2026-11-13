<?php

use app\models\ChangePasswordForm;
use app\widgets\AdminActiveForm;
use app\widgets\AdminButton;

/** @var ChangePasswordForm $model */

$this->title = 'Change password';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title mb-0">Change password</h2>
            </div>
            <div class="card-body">
                <?php $form = AdminActiveForm::begin(); ?>

                <?= $form->field($model, 'currentPassword')->passwordInput() ?>
                <?= $form->field($model, 'newPassword')->passwordInput() ?>
                <?= $form->field($model, 'confirmPassword')->passwordInput() ?>

                <?= AdminButton::submit('Update password') ?>

                <?php AdminActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>