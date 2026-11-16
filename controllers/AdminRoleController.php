<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\User;
use app\services\RbacPermissionPolicy;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
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

        return $this->render('../admin-user/roles', [
            'roles' => Yii::$app->authManager->getRoles(),
            'permissions' => Yii::$app->authManager->getPermissions(),
            'users' => User::find()->orderBy(['id' => SORT_ASC])->all(),
        ]);
    }

    public function actionCreate(): Response
    {
        $name = trim((string) $this->request->post('name'));
        $description = trim((string) $this->request->post('description'));

        $this->saveRole($name, $description, false);

        return $this->redirect(['index']);
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
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);
        $permissionNames = $this->request->post('permissions', []);

        if ($role === null || !is_array($permissionNames)) {
            throw new BadRequestHttpException('Role or permissions are invalid.');
        }

        if ($name === 'admin') {
            $permissionNames = array_keys($auth->getPermissions());
        }

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

        $auth->removeChildren($role);
        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->addChild($role, $permission);
        }

        return $this->redirect(['index']);
    }

    private function saveRole(string $name, string $description, bool $update): void
    {
        $auth = Yii::$app->authManager;
        if ($name === '' || !preg_match('/^[a-z][a-z0-9._-]{2,49}$/', $name)) {
            throw new BadRequestHttpException(
                'Role name must use lowercase letters, numbers, dots, dashes, or underscores.'
            );
        }
        if (!$update && $auth->getRole($name) !== null) {
            throw new BadRequestHttpException('Role name already exists.');
        }

        $role = $auth->createRole($name);
        $role->description = $description;
        $auth->add($role);
    }
}
