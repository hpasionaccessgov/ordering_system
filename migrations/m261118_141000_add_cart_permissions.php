<?php

use yii\db\Migration;
use yii\rbac\Permission;

class m261118_141000_add_cart_permissions extends Migration
{
    private const PERMISSIONS = [
        'cart.index',
        'cart.add',
        'cart.update',
        'cart.remove',
        'cart.checkout',
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $permissions = [];

        foreach (self::PERMISSIONS as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                if ($auth->getRole($name) !== null) {
                    throw new RuntimeException("Cannot create '{$name}': an RBAC role already uses that name.");
                }
                $permission = $auth->createPermission($name);
                $permission->description = $name;
                if (!$auth->add($permission)) {
                    throw new RuntimeException("Could not create '{$name}'.");
                }
            }
            $permissions[$name] = $permission;
        }

        $orderCreate = $auth->getPermission('order.create');
        if (!$orderCreate instanceof Permission) {
            throw new RuntimeException('The order.create permission is missing. Run the order RBAC migrations first.');
        }

        foreach ($auth->getRoles() as $role) {
            if (!$auth->hasChild($role, $orderCreate)) {
                continue;
            }
            foreach ($permissions as $name => $permission) {
                if (!$auth->hasChild($role, $permission) && !$auth->addChild($role, $permission)) {
                    throw new RuntimeException("Could not grant '{$name}' to '{$role->name}'.");
                }
            }
        }
    }

    public function safeDown(): bool
    {
        echo "Cart permissions cannot be automatically removed without losing role assignments.\n";

        return false;
    }
}
