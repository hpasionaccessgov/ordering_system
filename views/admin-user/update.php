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
        <div class="d-flex gap-3 mb-4 justify-content-between align-items-start">
            <div>
                <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Administration</p>
                <h1 class="mb-1 h3">Edit user details</h1>
                <p class="mb-0 text-body-secondary">
                    Update the account's name, username, and email address.
                </p>
            </div>
            <?php if (Yii::$app->user->can('user.index')) : ?>
                <?= Html::a('Back to user list', ['/admin-user/list'], [
                    'class' => 'btn btn-secondary',
                ]) ?>
            <?php endif; ?>
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
                    <?php if (Yii::$app->user->can('user.index')) : ?>
                        <?= Html::a('Cancel', ['/admin-user/list'], [
                            'class' => 'btn btn-secondary',
                        ]) ?>
                    <?php endif; ?>
                </div>

                <?php AdminActiveForm::end(); ?>
            </div>
        </section>
    </div>
</div>
