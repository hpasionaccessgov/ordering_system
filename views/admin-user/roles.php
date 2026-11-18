<?php

use app\models\RoleForm;
use app\services\RbacPermissionPolicy;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var RoleForm $roleForm */

$this->title = 'Roles';
$indexDependenciesJson = json_encode(
    RbacPermissionPolicy::indexDependents(),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT,
);
$viewDependenciesJson = json_encode(
    RbacPermissionPolicy::viewDependents(),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT,
);
$this->registerJs(<<<JS
(() => {
    const indexDependencies = {$indexDependenciesJson};
    const viewDependencies = {$viewDependenciesJson};
    document.querySelectorAll('form[data-role-permission-form]').forEach(form => {
        const permissions = Array.from(form.querySelectorAll('input[name="permissions[]"]'));
        const permissionByName = new Map(permissions.map(input => [input.dataset.permission, input]));
        const recoveryWarning = form.querySelector('[data-admin-recovery-warning]');

        const updateRecoveryWarning = () => {
            if (!recoveryWarning) {
                return;
            }

            const adminAccess = permissionByName.get('admin.access');
            const assignRole = permissionByName.get('user.assignRole');
            recoveryWarning.classList.toggle(
                'd-none',
                !adminAccess || !assignRole || adminAccess.checked || assignRole.checked,
            );
        };

        form.addEventListener('change', event => {
            const input = event.target;
            if (!(input instanceof HTMLInputElement) || !input.matches('input[name="permissions[]"]')) {
                return;
            }

            if (indexDependencies[input.dataset.permission]) {
                indexDependencies[input.dataset.permission].forEach(name => {
                    const dependent = permissionByName.get(name);
                    if (dependent) {
                        dependent.checked = input.checked;
                    }
                });
            }

            if (input.checked) {
                const module = input.dataset.permission.split('.')[0];
                const indexInput = permissionByName.get(module + '.index');
                if (indexInput && input.dataset.permission !== module + '.index') {
                    indexInput.checked = true;
                }

                Object.entries(viewDependencies).forEach(([viewPermission, dependents]) => {
                    if (dependents.includes(input.dataset.permission)) {
                        const viewInput = permissionByName.get(viewPermission);
                        if (viewInput) {
                            viewInput.checked = true;
                        }
                    }
                });
            }

            updateRecoveryWarning();
        });

        updateRecoveryWarning();
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
        if (window.bootstrap) {
            new bootstrap.Tooltip(element);
        }
    });
})();
JS);
$auth = Yii::$app->authManager;
$permissions = $auth->getPermissions();
$roles = $auth->getRoles();
$permissionModuleLabels = [
    'admin' => 'Administration',
    'staff' => 'Staff',
    'customer' => 'Customers',
    'user' => 'Users',
    'category' => 'Categories',
    'product' => 'Products',
    'order' => 'Orders',
];
$permissionDescriptions = [
    'admin.access' => 'Access the administrator dashboard.',
    'staff.access' => 'Access the staff dashboard and staff order context.',
    'customer.access' => 'Access customer features.',
    'user.index' => 'View the page and list of users.',
    'user.view' => 'View an individual user.',
    'user.create' => 'Create a new user.',
    'user.update' => 'Edit user information.',
    'user.delete' => 'Delete a user account.',
    'user.assignRole' => 'Assign roles to user accounts.',
    'category.index' => 'Access the category list page.',
    'category.view' => 'View categories and their details.',
    'category.create' => 'Create a category.',
    'category.update' => 'Edit a category.',
    'category.delete' => 'Delete a category not assigned to products.',
    'product.index' => 'View the page and product list.',
    'product.view' => 'View an individual product.',
    'product.create' => 'Create a new product.',
    'product.update' => 'Edit product information.',
    'product.archive' => 'Archive or restore products.',
    'product.delete' => 'Delete a product.',
    'order.index' => 'View the page and list of orders.',
    'order.view' => 'View an individual order.',
    'order.create' => 'Create a new order.',
    'order.update' => 'Edit order information.',
    'order.cancel' => 'Cancel an order.',
    'order.process' => 'Process an order status.',
    'order.receipt.print' => 'Print an order receipt.',
];
$permissionGroups = [];
foreach ($permissions as $permission) {
    $module = strstr($permission->name, '.', true) ?: 'other';
    $permissionGroups[$module][] = $permission;
}
$moduleOrder = array_keys($permissionModuleLabels);
uksort($permissionGroups, static function (string $left, string $right) use ($moduleOrder): int {
    $leftPosition = array_search($left, $moduleOrder, true);
    $rightPosition = array_search($right, $moduleOrder, true);

    return ($leftPosition === false ? PHP_INT_MAX : $leftPosition)
        <=> ($rightPosition === false ? PHP_INT_MAX : $rightPosition);
});
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Access control</p>
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
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
                'pattern' => '[a-z](?:[a-z0-9._]|-){2,49}',
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
        <div class="col-12">
            <?php AdminActiveForm::end(); ?>
        </div>
        <p class="small text-body-secondary mt-2 mb-0">
            Create a new role to assign permissions to users. Role names must be unique and use lowercase letters,
            numbers, dots, dashes, or underscores.
        </p>
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
                                'data-confirm-modal' => 'Delete this role?',
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
                    <?= Html::beginForm(
                        ['/admin-role/update', 'name' => $role->name],
                        'post',
                        ['data-role-permission-form' => 'true'],
                    ) ?>
                    <?= Html::hiddenInput('new_name', $role->name) ?>
                    <h3 class="small text-uppercase text-body-secondary">Permissions</h3>
                    <?php $children = array_keys($auth->getChildren($role->name)); ?>
                    <p class="small text-body-secondary">
                        Access permissions control dashboard and role context. Index permissions control module
                        lists, while view permissions control individual records.
                    </p>
                    <!-- <?php if ($auth->checkAccess(Yii::$app->user->id, 'user.assignRole')) : ?>
                        <div class="alert alert-warning small d-none" data-admin-recovery-warning role="alert">
                            If both administrator access and role management are disabled, access for the system will be lost. Restore them from the console with
                            <code>php yii rbac/restore-admin-access</code>.
                        </div>
                    <?php endif; ?> -->

                    <?php foreach ($permissionGroups as $module => $modulePermissions) : ?>
                        <section class="permission-module-group border rounded p-3 mb-3 bg-body-tertiary">
                            <h4 class="h6 text-body-secondary mb-2">
                                <?= Html::encode($permissionModuleLabels[$module] ?? ucfirst($module)) ?>
                            </h4>
                            <div class="row g-2">
                                <?php foreach ($modulePermissions as $permission) : ?>
                                    <div class="col-12 col-md-6">
                                        <?php
                                        $isChecked = in_array($permission->name, $children, true);
                                        ?>
                                        <?= Html::checkbox(
                                            'permissions[]',
                                            $isChecked,
                                            [
                                                'value' => $permission->name,
                                                'label' => Html::encode($permission->name),
                                                'title' => $permissionDescriptions[$permission->name]
                                                    ?? 'Permission: ' . $permission->name,
                                                'data-bs-toggle' => 'tooltip',
                                                'data-bs-placement' => 'top',
                                                'data-bs-title' => $permissionDescriptions[$permission->name]
                                                    ?? 'Permission: ' . $permission->name,
                                                'data-permission' => $permission->name,
                                                'uncheck' => null,
                                            ],
                                        ) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                    <div class="mt-3">
                        <label for="role-description-<?= Html::encode($role->name) ?>" class="form-label small text-uppercase text-body-secondary">
                            Description
                        </label>
                        <?= Html::input('text', 'description', $role->description, [
                            'id' => 'role-description-' . $role->name,
                            'class' => 'form-control form-control-sm',
                            'maxlength' => 255,
                            'aria-label' => 'Description for ' . $role->name,
                        ]) ?>
                    </div>
                    <?= Html::submitButton('Save changes', ['class' => 'btn btn-sm btn-primary mt-3']) ?>
                    <?= Html::endForm() ?>
                </div>
            </section>
        </div>
    <?php endforeach; ?>
</div>