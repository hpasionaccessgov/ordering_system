<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User[] $users */

$this->title = 'Roles';
$auth = Yii::$app->authManager;
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
        <?= Html::beginForm(['/admin-role/create'], 'post', ['class' => 'row g-2']) ?>
        <div class="col-12 col-md-4">
            <?= Html::input('text', 'name', '', ['class' => 'form-control', 'placeholder' => 'e.g. cashier', 'required' => true]) ?>
        </div>
        <div class="col-12 col-md-5">
            <?= Html::input('text', 'description', '', ['class' => 'form-control', 'placeholder' => 'Role description']) ?>
        </div>
        <div class="col-12 col-md-3">
            <?= Html::submitButton('Create role', ['class' => 'btn btn-primary w-100']) ?>
        </div>
        <?= Html::endForm() ?>
    </div>
</section>

<section class="card admin-panel mb-4">
    <div class="card-body">
        <h2 class="h5">Assign roles to users</h2>
        <p class="text-body-secondary">Each account receives one application role.</p>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>User</th><th>Email</th><th>Role</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $assigned = $auth->getRolesByUser($user->id);
                    $selected = count($assigned) === 1 ? array_key_first($assigned) : '';
                    ?>
                    <tr>
                        <td><?= Html::encode($user->username) ?></td>
                        <td><?= Html::encode($user->email) ?></td>
                        <td>
                            <?php if ((string) $user->id === (string) Yii::$app->user->id): ?>
                                <span class="text-body-secondary">Current account</span>
                            <?php else: ?>
                                <?= Html::beginForm(['/admin-user/assign-role', 'id' => $user->id], 'post', ['class' => 'd-flex gap-2']) ?>
                                <?= Html::dropDownList('role', $selected, [
                                    '' => 'Select role',
                                    'admin' => 'Admin',
                                    'staff' => 'Staff',
                                    'customer' => 'Customer',
                                ], ['class' => 'form-select form-select-sm', 'required' => true]) ?>
                                <?= Html::submitButton('Save', ['class' => 'btn btn-sm btn-primary']) ?>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div class="row g-4">
    <?php foreach ($roles as $role): ?>
        <div class="col-12 col-lg-6">
            <section class="card admin-panel h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h2 class="h5 mb-1"><?= Html::encode($role->name) ?></h2>
                            <p class="text-body-secondary"><?= Html::encode($role->description) ?></p>
                        </div>
                        <?php if (!in_array($role->name, ['admin', 'staff', 'customer'], true)): ?>
                            <?= Html::beginForm(['/admin-role/delete', 'name' => $role->name], 'post') ?>
                            <?= Html::submitButton('Delete', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'Delete this role?']) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </div>
                    <p class="small text-body-secondary mb-2">Assigned users: <?= count($auth->getUserIdsByRole($role->name)) ?></p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($auth->getChildren($role->name) as $child): ?>
                            <span class="badge text-bg-secondary"><?= Html::encode($child->name) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <h3 class="small text-uppercase text-body-secondary">Permissions</h3>
                    <?= Html::beginForm(['/admin-role/update-permissions', 'name' => $role->name], 'post') ?>
                    <div class="row g-2">
                        <?php $children = array_keys($auth->getChildren($role->name)); ?>
                        <?php foreach ($permissions as $permission): ?>
                            <div class="col-12 col-md-6">
                                <?= Html::checkbox(
                                    'permissions[]',
                                    in_array($permission->name, $children, true),
                                    [
                                        'value' => $permission->name,
                                        'label' => Html::encode($permission->name),
                                    ],
                                ) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?= Html::submitButton('Save permissions', ['class' => 'btn btn-sm btn-outline-primary mt-3']) ?>
                    <?= Html::endForm() ?>
                    <hr>
                    <?= Html::beginForm(['/admin-role/update', 'name' => $role->name], 'post', ['class' => 'row g-2']) ?>
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
