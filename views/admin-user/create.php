<?php

use app\models\AdminUserCreateForm;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var AdminUserCreateForm $model */
/** @var yii\rbac\Role[] $roles */

$this->title = 'Create account';
$roleOptions = [];
foreach ($roles as $role) {
    $roleOptions[$role->name] = $role->description !== ''
        ? $role->description . ' (' . $role->name . ')'
        : ucfirst($role->name);
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Administration</p>
                <h1 class="h3 mb-1">Create account</h1>
                <p class="text-body-secondary mb-0">Create a user and assign an existing role.</p>
            </div>
            <?= Html::a('Back to user list', ['/admin-user/list'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>

        <section class="card admin-panel">
            <div class="card-body">
                <?php $form = AdminActiveForm::begin(); ?>

                <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

                <?= $form->field($model, 'fullName')->textInput([
                    'maxlength' => true,
                    'autocomplete' => 'name',
                    'autofocus' => true,
                ]) ?>

                <?= $form->field($model, 'username')->textInput([
                    'maxlength' => true,
                    'autocomplete' => 'username',
                ]) ?>

                <?= $form->field($model, 'email')->input('email', [
                    'maxlength' => true,
                    'autocomplete' => 'email',
                ]) ?>

                <?= $form->field($model, 'password')->passwordInput([
                    'autocomplete' => 'new-password',
                ]) ?>

                <?= $form->field($model, 'passwordConfirm')->passwordInput([
                    'autocomplete' => 'new-password',
                ]) ?>

                <?= $form->field($model, 'roleName')->dropDownList($roleOptions, [
                    'prompt' => 'Select role',
                ]) ?>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <?= Html::submitButton('Create account', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Cancel', ['/admin-user/list'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>

                <?php AdminActiveForm::end(); ?>
            </div>
        </section>
    </div>
</div>
