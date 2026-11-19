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

    const permissionsByRole = new Map();
    form.querySelectorAll('input[name^="permissions["]').forEach(input => {
        const role = input.name.slice('permissions['.length, input.name.indexOf(']'));
        if (!permissionsByRole.has(role)) {
            permissionsByRole.set(role, new Map());
        }
        permissionsByRole.get(role).set(input.dataset.permission, input);
    });

    form.addEventListener('change', event => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || !input.matches('input[name^="permissions["]')) {
            return;
        }

        const role = input.name.slice('permissions['.length, input.name.indexOf(']'));
        const permissionByName = permissionsByRole.get(role);
        if (!permissionByName) {
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
    $roleUserCounts[$role->name] = count($auth->getUserIdsByRole($role->name));
}
$columnCount = 2 + count($roleList);
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

<section class="card admin-panel">
    <div class="card-header">
        <h2 class="h5 mb-1">Permission matrix</h2>
        <p class="text-body-secondary mb-0">
            Grant permissions to every role in one table. Access permissions control dashboards and role context;
            index permissions control module lists, and view permissions control individual records.
        </p>
    </div>
    <?= Html::beginForm(['/admin-role/update-matrix'], 'post', ['data-role-matrix-form' => 'true']) ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 cafebara-data-table">
            <thead>
                <tr>
                    <th scope="col">Permission</th>
                    <th scope="col">Description</th>
                    <?php foreach ($roleList as $role) : ?>
                        <th scope="col" class="text-center">
                            <span class="d-block"><?= Html::encode($role->name) ?></span>
                            <span class="d-block fw-normal text-body-secondary">
                                <?= $roleUserCounts[$role->name] ?> user<?= $roleUserCounts[$role->name] === 1 ? '' : 's' ?>
                            </span>
                            <?php if (!in_array($role->name, $builtInRoles, true)) : ?>
                                <?= Html::submitButton('Delete', [
                                    'form' => 'delete-role-' . $role->name,
                                    'class' => 'btn btn-sm btn-outline-danger mt-1',
                                    'data-confirm-modal' => 'Delete this role?',
                                ]) ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissionGroups as $module => $modulePermissions) : ?>
                    <tr class="cafebara-table-group">
                        <th scope="colgroup" colspan="<?= $columnCount ?>">
                            <?= Html::encode($permissionModuleLabels[$module] ?? ucfirst($module)) ?>
                        </th>
                    </tr>
                    <?php foreach ($modulePermissions as $permission) : ?>
                        <tr>
                            <td><code><?= Html::encode($permission->name) ?></code></td>
                            <td class="small text-body-secondary">
                                <?= Html::encode(
                                    $permissionDescriptions[$permission->name]
                                        ?? 'Permission: ' . $permission->name,
                                ) ?>
                            </td>
                            <?php foreach ($roleList as $role) : ?>
                                <td class="text-center">
                                    <?= Html::checkbox(
                                        'permissions[' . $role->name . '][]',
                                        in_array($permission->name, $roleChildren[$role->name], true),
                                        [
                                            'value' => $permission->name,
                                            'data-permission' => $permission->name,
                                            'data-role' => $role->name,
                                            'aria-label' => 'Grant ' . $permission->name . ' to ' . $role->name,
                                            'uncheck' => null,
                                        ],
                                    ) ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <tr>
                    <th scope="row" class="small text-uppercase text-body-secondary">Description</th>
                    <td class="small text-body-secondary">Shown on role badges.</td>
                    <?php foreach ($roleList as $role) : ?>
                        <td>
                            <?= Html::input('text', 'descriptions[' . $role->name . ']', $role->description, [
                                'class' => 'form-control form-control-sm',
                                'maxlength' => 255,
                                'aria-label' => 'Description for ' . $role->name,
                            ]) ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-body d-flex justify-content-end">
        <?= Html::submitButton('Save changes', ['class' => 'btn btn-primary']) ?>
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