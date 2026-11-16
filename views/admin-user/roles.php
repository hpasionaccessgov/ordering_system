<?php

use app\models\RoleForm;
use app\services\RbacPermissionPolicy;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var RoleForm $roleForm */

$this->title = 'Roles';
$auth = Yii::$app->authManager;
$permissions = $auth->getPermissions();
$roles = $auth->getRoles();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Access control</p>
        <h1 class="h3 mb-0">Roles</h1>
    </div>
</div>

<section class="card admin-panel mb-4">
    <div class="card-body">
        <h2 class="h5">Create role</h2>
        <?php $form = AdminActiveForm::begin([
            'action' => ['/admin-role/create'],
            'method' => 'post',
            'options' => ['class' => 'row g-2'],
        ]); ?>

        <div class="col-12">
            <?= $form->errorSummary(
                $roleForm,
                ['class' => 'alert alert-danger'],
            ) ?>
        </div>
        <div class="col-12 col-md-4">
            <?= $form->field($roleForm, 'name', ['options' => ['class' => 'mb-0']])->textInput([
                'placeholder' => 'e.g. cashier',
                'maxlength' => 50,
                'pattern' => '[a-z][a-z0-9._-]{2,49}',
                'title' => 'Start with a lowercase letter; use 3-50 lowercase letters, numbers, dots, dashes, '
                    . 'or underscores.',
                'autocapitalize' => 'none',
                'spellcheck' => 'false',
            ]) ?>
        </div>
        <div class="col-12 col-md-5">
            <?= $form->field($roleForm, 'description', ['options' => ['class' => 'mb-0']])->textInput([
                'placeholder' => 'Role description',
                'maxlength' => 255,
            ]) ?>
        </div>
        <div class="col-12 col-md-3 d-flex align-items-end">
            <?= Html::submitButton('Create role', ['class' => 'btn btn-primary w-100']) ?>
        </div>

        <?php AdminActiveForm::end(); ?>
    </div>
</section>

<div class="row g-4">
    <?php foreach ($roles as $role) : ?>
        <div class="col-12 col-lg-6">
            <section class="card admin-panel h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h5 mb-1"><?= Html::encode($role->name) ?></h2>
                            <p class="text-body-secondary"><?= Html::encode($role->description) ?></p>
                        </div>
                        <?php if (!in_array($role->name, ['admin', 'staff', 'customer'], true)) : ?>
                            <?= Html::beginForm(['/admin-role/delete', 'name' => $role->name], 'post') ?>
                            <?= Html::submitButton('Delete', [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data-confirm' => 'Delete this role?',
                            ]) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </div>
                    <p class="small text-body-secondary mb-2">
                        Assigned users: <?= count($auth->getUserIdsByRole($role->name)) ?>
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($auth->getChildren($role->name) as $child) : ?>
                            <span class="badge text-bg-secondary"><?= Html::encode($child->name) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <h3 class="small text-uppercase text-body-secondary">Permissions</h3>
                    <?= Html::beginForm(['/admin-role/update-permissions', 'name' => $role->name], 'post') ?>
                    <div class="row g-2">
                        <?php $isAdminRole = $role->name === 'admin'; ?>
                        <?php $children = array_keys($auth->getChildren($role->name)); ?>
                        <?php foreach ($permissions as $permission) : ?>
                            <div class="col-12 col-md-6">
                                <?php
                                $canAssignPermission = RbacPermissionPolicy::isAllowed(
                                    $role->name,
                                    $permission->name,
                                );
                                $isChecked = $isAdminRole || in_array($permission->name, $children, true);
                                ?>
                                <?= Html::checkbox(
                                    'permissions[]',
                                    $isChecked,
                                    [
                                        'value' => $permission->name,
                                        'label' => Html::encode($permission->name),
                                        'title' => $permission->name,
                                        'disabled' => $isAdminRole || !$canAssignPermission,
                                        'uncheck' => null,
                                    ],
                                ) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($isAdminRole) : ?>
                        <p class="small text-body-secondary mt-3 mb-0">
                            Administrator permissions are fixed and always enabled.
                        </p>
                    <?php else : ?>
                        <?= Html::submitButton(
                            'Save permissions',
                            ['class' => 'btn btn-sm btn-outline-primary mt-3']
                        ) ?>
                    <?php endif; ?>
                    <?= Html::endForm() ?>
                    <hr>
                    <?= Html::beginForm(
                        ['/admin-role/update', 'name' => $role->name],
                        'post',
                        ['class' => 'row g-2']
                    ) ?>
                    <?= Html::hiddenInput('new_name', $role->name) ?>
                    <div class="col-12">
                        <?= Html::input('text', 'description', $role->description, [
                            'class' => 'form-control form-control-sm',
                            'aria-label' => 'Description for ' . $role->name,
                        ]) ?>
                    </div>
                    <div class="col-12">
                        <?= Html::submitButton('Save description', ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    </div>
                    <?= Html::endForm() ?>
                </div>
            </section>
        </div>
    <?php endforeach; ?>
</div>