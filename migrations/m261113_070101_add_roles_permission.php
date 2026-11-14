<?php

use yii\db\Migration;

class m261113_070101_add_roles_permission extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $permissionNames = [
            'admin.access',
            'staff.access',
            'customer.access',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'user.assignRole',
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'order.view',
            'order.create',
            'order.update',
            'order.cancel',
            'order.process',
        ];

        $permissions = [];
        foreach ($permissionNames as $name) {
            $permission = $auth->createPermission($name);
            $permission->description = $name;
            $auth->add($permission);
            $permissions[$name] = $permission;
        }

        $admin = $auth->createRole('admin');
        $staff = $auth->createRole('staff');
        $customer = $auth->createRole('customer');

        $auth->add($admin);
        $auth->add($staff);
        $auth->add($customer);

        foreach ($permissions as $permission) {
            $auth->addChild($admin, $permission);
        }

        foreach (
            [
                'staff.access',
                'product.view',
                'product.create',
                'product.delete',
                'order.view',
                'order.update',
                'order.process',
            ] as $permissionName
        ) {
            $auth->addChild($staff, $permissions[$permissionName]);
        }

        foreach (
            [
                'customer.access',
                'product.view',
                'order.view',
                'order.create',
                'order.update',
                'order.cancel',
            ] as $permissionName
        ) {
            $auth->addChild($customer, $permissions[$permissionName]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m261113_070101_add_roles_permission cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261113_070101_add_roles_permission cannot be reverted.\n";

        return false;
    }
    */
}
