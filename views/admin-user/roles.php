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
    const form = document.querySelector('form[data-role-matrix-form]');
    if (!form) {
        return;
    }

    const syncToggles = card => {
        const inputs = [...card.querySelectorAll('input[name^="permissions["]')];
        const grantAll = card.querySelector('[data-grant-all]');
        if (grantAll) {
            grantAll.checked = inputs.length > 0 && inputs.every(input => input.checked);
        }
        card.querySelectorAll('[data-permission-group]').forEach(group => {
            const toggle = group.querySelector('[data-module-toggle]');
            const groupInputs = [...group.querySelectorAll('input[name^="permissions["]')];
            if (toggle) {
                toggle.checked = groupInputs.length > 0 && groupInputs.every(input => input.checked);
            }
        });
    };

    form.addEventListener('change', event => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const card = input.closest('[data-role-card]');
        if (!card) {
            return;
        }

        const permissionByName = new Map(
            [...card.querySelectorAll('input[name^="permissions["]')]
                .map(item => [item.dataset.permission, item]),
        );

        if (input.matches('[data-grant-all]')) {
            permissionByName.forEach(item => { item.checked = input.checked; });
            syncToggles(card);
            return;
        }

        if (input.matches('[data-module-toggle]')) {
            input.closest('[data-permission-group]')
                .querySelectorAll('input[name^="permissions["]')
                .forEach(item => { item.checked = input.checked; });
            syncToggles(card);
            return;
        }

        if (!input.matches('input[name^="permissions["]')) {
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

        syncToggles(card);
    });

    form.querySelectorAll('[data-role-card]').forEach(syncToggles);
})();
JS);
$roleUsers = $roleUsers ?? [];
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
    'cart' => 'Cart & checkout',
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
    'cart.index' => 'View and manage the shopping cart page.',
    'cart.add' => 'Add active products to a cart.',
    'cart.update' => 'Change quantities in a cart.',
    'cart.remove' => 'Remove products from a cart.',
    'cart.checkout' => 'Place an order from a cart and view its confirmation.',
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
$roleList = array_values($roles);
$builtInRoles = ['admin', 'staff', 'customer'];
$roleChildren = [];
$roleUserCounts = [];
foreach ($roleList as $role) {
    $roleChildren[$role->name] = array_keys($auth->getChildren($role->name));
    $roleUserCounts[$role->name] = count($roleUsers[$role->name] ?? []);
}
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
        <div class="col-12 col-md-3 d-flex flex-column">
            <label class="form-label invisible d-none d-md-block" aria-hidden="true">Action</label>
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

<section class="card admin-panel">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h2 class="h5 mb-1">Roles &amp; permissions</h2>
            <p class="text-body-secondary mb-0">
                Each role is its own card, so the page never grows sideways. Tick the permissions a role needs,
                or use <strong>Grant all permissions</strong> for full access, then save once.
            </p>
        </div>
        <?= Html::submitButton('Save changes', [
            'form' => 'role-matrix-form',
            'class' => 'btn btn-primary ms-auto',
        ]) ?>
    </div>
    <?= Html::beginForm(['/admin-role/update-matrix'], 'post', [
        'id' => 'role-matrix-form',
        'data-role-matrix-form' => 'true',
    ]) ?>
    <div class="card-body d-flex flex-column gap-4">
        <?php foreach ($roleList as $role) : ?>
            <?php $isBuiltIn = in_array($role->name, $builtInRoles, true); ?>
            <section class="border rounded-3 p-3" data-role-card data-role="<?= Html::encode($role->name) ?>">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h3 class="h6 mb-1">
                            <?= Html::encode($role->name) ?>
                            <?php if ($isBuiltIn) : ?>
                                <span class="badge text-bg-secondary ms-1">Built-in</span>
                            <?php endif; ?>
                        </h3>
                        <p class="small text-body-secondary mb-0">
                            <strong><?= $roleUserCounts[$role->name] ?></strong>
                            user<?= $roleUserCounts[$role->name] === 1 ? '' : 's' ?>:
                            <?= $roleUsers[$role->name] === []
                                ? 'none assigned'
                                : Html::encode(implode(', ', $roleUsers[$role->name])) ?>
                        </p>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input" role="switch"
                                   id="grant-all-<?= Html::encode($role->name) ?>" data-grant-all
                                   aria-label="Grant all permissions to <?= Html::encode($role->name) ?>">
                            <label class="form-check-label small" for="grant-all-<?= Html::encode($role->name) ?>">
                                Grant all permissions
                            </label>
                        </div>
                        <?php if (!$isBuiltIn) : ?>
                            <?= Html::submitButton('Delete role', [
                                'form' => 'delete-role-' . $role->name,
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data-confirm-modal' => 'Delete this role?',
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label small fw-semibold" for="role-desc-<?= Html::encode($role->name) ?>">
                        Description
                    </label>
                    <?= Html::input('text', 'descriptions[' . $role->name . ']', $role->description, [
                        'id' => 'role-desc-' . Html::encode($role->name),
                        'class' => 'form-control form-control-sm',
                        'maxlength' => 255,
                        'placeholder' => 'Shown on role badges',
                    ]) ?>
                </div>

                <div class="row g-2 mt-1">
                    <?php foreach ($permissionGroups as $module => $modulePermissions) : ?>
                        <div class="col-12 col-lg-6 col-xxl-4" data-permission-group>
                            <div class="border rounded-2 p-2 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-uppercase fw-semibold text-body-secondary">
                                        <?= Html::encode($permissionModuleLabels[$module] ?? ucfirst($module)) ?>
                                    </span>
                                    <label class="small text-body-secondary mb-0">
                                        <input type="checkbox" class="form-check-input me-1" data-module-toggle>
                                        All
                                    </label>
                                </div>
                                <?php foreach ($modulePermissions as $permission) : ?>
                                    <?php $permissionId = 'perm-' . $role->name . '-' . $permission->name; ?>
                                    <div class="form-check">
                                        <?= Html::checkbox(
                                            'permissions[' . $role->name . '][]',
                                            in_array($permission->name, $roleChildren[$role->name], true),
                                            [
                                                'id' => $permissionId,
                                                'value' => $permission->name,
                                                'class' => 'form-check-input',
                                                'data-permission' => $permission->name,
                                                'data-role' => $role->name,
                                                'uncheck' => null,
                                                'style' => 'cursor: pointer;',
                                            ],
                                        ) ?>
                                        <label class="form-check-label small" for="<?= Html::encode($permissionId) ?>"
                                               title="<?= Html::encode($permissionDescriptions[$permission->name] ?? $permission->name) ?>"
                                               style="cursor: pointer;">
                                            <code><?= Html::encode($permission->name) ?></code>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
    <?= Html::endForm() ?>
</section>

<?php foreach ($roleList as $role) : ?>
    <?php if (!in_array($role->name, $builtInRoles, true)) : ?>
        <?= Html::beginForm(['/admin-role/delete', 'name' => $role->name], 'post', [
            'id' => 'delete-role-' . $role->name,
        ]) ?>
        <?= Html::endForm() ?>
    <?php endif; ?>
<?php endforeach; ?>