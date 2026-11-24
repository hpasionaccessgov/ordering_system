<?php

use app\models\DeactivateForm;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var DeactivateForm $model */

$this->title = 'Deactivate account';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card border-danger">
            <div class="card-header text-bg-danger">
                <h2 class="mb-0 card-title">Deactivate account</h2>
            </div>
            <div class="card-body">
                <p>
                    Deactivating your account will sign you out and prevent future logins.
                </p>

                <?php $form = AdminActiveForm::begin(['method' => 'POST']); ?>

                <?= $form->errorSummary($model) ?>

                <?= $form->field($model, 'currentPassword')
                    ->passwordInput()
                    ->label('Confirm your current password') ?>

                <?= Html::submitButton('Deactivate my account', ['class' => 'btn btn-danger']) ?>

                <?php AdminActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>