<?php

use yii\db\Migration;
use yii\rbac\Role;

class m261116_095000_add_order_receipt_print_permission extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission('order.receipt.print');

        if ($permission === null) {
            $permission = $auth->createPermission('order.receipt.print');
            $permission->description = 'order.receipt.print';
            $auth->add($permission);
        }

        foreach (['admin', 'staff'] as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role instanceof Role && !$auth->hasChild($role, $permission)) {
                $auth->addChild($role, $permission);
            }
        }

        $customer = $auth->getRole('customer');
        if ($customer instanceof Role && $auth->hasChild($customer, $permission)) {
            $auth->removeChild($customer, $permission);
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission('order.receipt.print');

        if ($permission === null) {
            return;
        }

        foreach (['admin', 'staff', 'customer'] as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role !== null && $auth->hasChild($role, $permission)) {
                $auth->removeChild($role, $permission);
            }
        }

        $auth->remove($permission);
    }
}
