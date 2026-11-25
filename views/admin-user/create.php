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
        <div class="d-flex gap-3 mb-4 justify-content-between align-items-start">
            <div>
                <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Administration</p>
                <h1 class="mb-1 h3">Create account</h1>
                <p class="mb-0 text-body-secondary">Create a user and assign an existing role.</p>
            </div>
            <?php if (Yii::$app->user->can('user.index')) : ?>
                <?= Html::a('Back to user list', ['/admin/users'], ['class' => 'btn btn-secondary']) ?>
            <?php endif; ?>
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
                    <?php if (Yii::$app->user->can('user.index')) : ?>
                        <?= Html::a('Cancel', ['/admin/users'], ['class' => 'btn btn-secondary']) ?>
                    <?php endif; ?>
                </div>

                <?php AdminActiveForm::end(); ?>
            </div>
        </section>
    </div>
</div>
