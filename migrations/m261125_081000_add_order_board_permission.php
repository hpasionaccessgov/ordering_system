<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\rbac\Permission;
use yii\rbac\Role;

class m261125_081000_add_order_board_permission extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission('order.board');
        if ($permission === null) {
            $permission = $auth->createPermission('order.board');
            $permission->description = 'order.board';
            if (!$auth->add($permission)) {
                throw new RuntimeException("Could not create permission 'order.board'.");
            }
        }

        foreach (['staff', 'admin', 'customer'] as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role instanceof Role && !$auth->hasChild($role, $permission)) {
                if (!$auth->addChild($role, $permission)) {
                    throw new RuntimeException("Could not grant 'order.board' to '{$roleName}'.");
                }
            }
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission('order.board');
        if (!$permission instanceof Permission) {
            return;
        }

        foreach (['staff', 'admin', 'customer'] as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role instanceof Role && $auth->hasChild($role, $permission)) {
                $auth->removeChild($role, $permission);
            }
        }
        $auth->remove($permission);
    }
}
