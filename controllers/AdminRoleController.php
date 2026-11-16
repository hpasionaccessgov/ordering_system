<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\RoleForm;
use app\services\RbacPermissionPolicy;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class AdminRoleController extends Controller
{
    public function behaviors(): array
    {
        return [
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

    public function actionCreate(): Response
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
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);

        if ($role === null) {
            throw new BadRequestHttpException('Role not found.');
        }
        if (in_array($name, ['admin', 'staff', 'customer'], true) && $newName !== $name) {
            throw new BadRequestHttpException('Built-in role names cannot be changed.');
        }
        if ($newName !== $name && $auth->getRole($newName) !== null) {
            throw new BadRequestHttpException('Role name already exists.');
        }

        if ($newName !== $name) {
            $role->name = $newName;
            $auth->remove($auth->getRole($name));
            $auth->add($role);
        }
        $role->description = $description;
        $auth->update($role->name, $role);

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

        return $this->redirect(['index']);
    }

    public function actionUpdatePermissions(string $name): Response
    {
        if ($name === 'admin') {
            throw new ForbiddenHttpException('Administrator permissions cannot be changed.');
        }

        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);
        $permissionNames = $this->request->post('permissions', []);

        if ($role === null || !is_array($permissionNames)) {
            throw new BadRequestHttpException('Role or permissions are invalid.');
        }

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
            if (!RbacPermissionPolicy::isAllowed($name, $permissionName)) {
                throw new BadRequestHttpException(
                    "The permission '{$permissionName}' cannot be assigned to the '{$name}' role.",
                );
            }
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $auth->removeChildren($role);
            foreach ($permissionNames as $permissionName) {
                $permission = $auth->getPermission($permissionName);
                if (!$auth->addChild($role, $permission)) {
                    throw new \RuntimeException("Could not assign permission '{$permissionName}'.");
                }
            }
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
