<?php

use app\models\User;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var User $model */

$this->title = 'Edit user details';
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Administration</p>
                <h1 class="h3 mb-1">Edit user details</h1>
                <p class="text-body-secondary mb-0">
                    Update the account's name, username, and email address.
                </p>
            </div>
            <?= Html::a('Back to user list', ['/admin-user/list'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <section class="card admin-panel">
            <div class="card-body">
                <p class="small text-body-secondary">
                    Password and assigned role are managed separately and will not be changed here.
                </p>

                <?php $form = AdminActiveForm::begin(); ?>

                <?= $form->field($model, 'full_name')->textInput([
                    'maxlength' => true,
                    'autocomplete' => 'name',
                ]) ?>

                <?= $form->field($model, 'username')->textInput([
                    'maxlength' => true,
                    'autocomplete' => 'username',
                ]) ?>

                <?= $form->field($model, 'email')->input('email', [
                    'maxlength' => true,
                    'autocomplete' => 'email',
                ]) ?>

                <?= $form->field($model, 'status')->dropDownList([
                    User::STATUS_INACTIVE => 'Inactive',
                    User::STATUS_ACTIVE => 'Allowed',
                    User::STATUS_DISABLED => 'Blocked',
                ]) ?>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <?= Html::submitButton('Save changes', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Cancel', ['/admin-user/list'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>

                <?php AdminActiveForm::end(); ?>
            </div>
        </section>
    </div>
</div>
