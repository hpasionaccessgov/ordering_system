<?php

declare(strict_types=1);

namespace app\controllers;

use app\filters\StaffAccessFilter;
use app\models\RoleForm;
use app\services\RbacPermissionPolicy;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rbac\Role;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class AdminRoleController extends Controller
{
    public function behaviors(): array
    {
        return [
            'staffAccess' => [
                'class' => StaffAccessFilter::class,
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['user.assignRole']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'update' => ['post'],
                    'update-permissions' => ['post'],
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';

        return $this->renderRoles(new RoleForm());
    }

    public function actionCreate(): Response|string
    {
        $model = new RoleForm();
        if ($model->load($this->request->post()) && $model->validate()) {
            $auth = Yii::$app->authManager;
            $role = $auth->createRole($model->name);
            $role->description = $model->description;
            if (!$auth->add($role)) {
                $model->addError('name', 'The role could not be created. Please try again.');
                $this->layout = 'admin';

                return $this->renderRoles($model);
            }

            Yii::$app->session->setFlash('success', "Role '{$role->name}' was created.");

            return $this->redirect(['index']);
        }

        $this->layout = 'admin';

        return $this->renderRoles($model);
    }

    public function actionUpdate(string $name): Response
    {
        $newName = trim((string) $this->request->post('new_name'));
        $description = trim((string) $this->request->post('description'));
        $permissionNames = $this->request->post('permissions', []);
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);

        if ($role === null) {
            throw new BadRequestHttpException('Role not found.');
        }
        if (!is_array($permissionNames)) {
            throw new BadRequestHttpException('Permissions are invalid.');
        }
        if (in_array($name, ['admin', 'staff', 'customer'], true) && $newName !== $name) {
            throw new BadRequestHttpException('Built-in role names cannot be changed.');
        }
        if ($newName !== $name && $auth->getRole($newName) !== null) {
            throw new BadRequestHttpException('Role name already exists.');
        }

        $permissionNames = $this->normalizePermissionNames($permissionNames, $role->name);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($newName !== $name) {
                $role->name = $newName;
                $auth->remove($auth->getRole($name));
                $auth->add($role);
            }
            $role->description = $description;
            $auth->update($role->name, $role);

            $this->syncRolePermissions($role, $permissionNames);

            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        Yii::$app->session->setFlash('success', "Role '{$role->name}' was updated.");

        return $this->redirect(['index']);
    }

    public function actionDelete(string $name): Response
    {
        if (in_array($name, ['admin', 'staff', 'customer'], true)) {
            throw new BadRequestHttpException('Built-in roles cannot be deleted.');
        }

        $role = Yii::$app->authManager->getRole($name);
        if ($role !== null) {
            Yii::$app->authManager->remove($role);
        }

        Yii::$app->session->setFlash('success', "Role '{$name}' was deleted.");

        return $this->redirect(['index']);
    }

    public function actionUpdatePermissions(string $name): Response
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);
        $permissionNames = $this->request->post('permissions', []);

        if ($role === null || !is_array($permissionNames)) {
            throw new BadRequestHttpException('Role or permissions are invalid.');
        }

        $permissionNames = $this->normalizePermissionNames($permissionNames, $role->name);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->syncRolePermissions($role, $permissionNames);
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        Yii::$app->session->setFlash('success', "Permissions for '{$name}' were updated.");

        return $this->redirect(['index']);
    }

    /**
     * Validates the submitted permission names and returns them as a list.
     *
     * @param array $permissionNames the raw permission names from the request.
     * @return string[] the validated permission names.
     */
    private function normalizePermissionNames(array $permissionNames, string $roleName): array
    {
        $auth = Yii::$app->authManager;
        $permissionNames = array_values(array_filter(
            $permissionNames,
            static fn ($permissionName): bool => $permissionName !== '0',
        ));

        foreach ($permissionNames as $permissionName) {
            if (!is_string($permissionName)) {
                throw new BadRequestHttpException('Permission names must be strings.');
            }
            if ($auth->getPermission($permissionName) === null) {
                throw new BadRequestHttpException("Permission '{$permissionName}' does not exist.");
            }
        }

        return RbacPermissionPolicy::normalizeRolePermissions($permissionNames, $roleName);
    }

    /**
     * Replaces the role's permissions with the given ones.
     *
     * @param Role $role the role to update.
     * @param string[] $permissionNames the validated permission names to assign.
     */
    private function syncRolePermissions(Role $role, array $permissionNames): void
    {
        $auth = Yii::$app->authManager;
        $auth->removeChildren($role);
        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            if (!$auth->addChild($role, $permission)) {
                throw new \RuntimeException("Could not assign permission '{$permissionName}'.");
            }
        }
    }

    private function renderRoles(RoleForm $roleForm): string
    {
        $auth = Yii::$app->authManager;

        return $this->render('../admin-user/roles', [
            'roles' => $auth->getRoles(),
            'permissions' => $auth->getPermissions(),
            'roleForm' => $roleForm,
        ]);
    }
}
