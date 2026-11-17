<?php

use yii\db\Migration;

class m261117_090000_add_resource_index_permissions extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $indexPermissions = [];

        foreach (['order.index', 'product.index', 'user.index'] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $name;
                if (!$auth->add($permission)) {
                    throw new \RuntimeException("Could not create the '{$name}' permission.");
                }
            }
            $indexPermissions[$name] = $permission;
        }

        $legacyPermissions = [
            'order.index' => ['order.view'],
            'product.index' => [
                'product.view',
                'product.create',
                'product.update',
                'product.archive',
                'product.delete',
            ],
            'user.index' => [
                'user.view',
                'user.create',
                'user.update',
                'user.delete',
                'user.assignRole',
            ],
        ];

        foreach ($auth->getRoles() as $role) {
            $children = array_keys($auth->getChildren($role->name));

            foreach ($legacyPermissions as $indexName => $oldPermissions) {
                if (
                    array_intersect($oldPermissions, $children) !== []
                    && !$auth->hasChild($role, $indexPermissions[$indexName])
                ) {
                    if (!$auth->addChild($role, $indexPermissions[$indexName])) {
                        throw new \RuntimeException(
                            "Could not grant '{$indexName}' to the '{$role->name}' role.",
                        );
                    }
                }
            }
        }

        \app\services\AdminAccessRecovery::restoreAdminRoleAccess();
    }

    public function safeDown(): bool
    {
        echo "m261117_090000_add_resource_index_permissions cannot be reverted.\n";

        return false;
    }
}
