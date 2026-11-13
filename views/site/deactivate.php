<?php

use app\models\DeactivateForm;
use app\widgets\AdminActiveForm;
use app\widgets\AdminButton;

/** @var DeactivateForm $model */

$this->title = 'Deactivate account';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card border-danger">
            <div class="card-header text-bg-danger">
                <h2 class="card-title mb-0">Deactivate account</h2>
            </div>
            <div class="card-body">
                <p>
                    Deactivating your account will sign you out and prevent future logins.
                </p>

                <?php $form = AdminActiveForm::begin(['method' => 'post']); ?>

                <?= $form->errorSummary($model) ?>

                <?= $form->field($model, 'currentPassword')
                    ->passwordInput()
                    ->label('Confirm your current password') ?>

                <?= AdminButton::submit('Deactivate my account', 'danger') ?>

                <?php AdminActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>