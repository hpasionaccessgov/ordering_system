<?php

use yii\db\Migration;
use yii\rbac\Role;

class m261118_105000_add_category_permissions extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');
        if (!$admin instanceof Role) {
            throw new \RuntimeException('The admin role is missing. Run the RBAC migrations first.');
        }

        foreach (['category.index', 'category.view', 'category.create', 'category.update', 'category.delete'] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                if ($auth->getRole($name) !== null) {
                    throw new \RuntimeException("Cannot create '{$name}': an RBAC role already uses that name.");
                }

                $permission = $auth->createPermission($name);
                $permission->description = $name;
                if (!$auth->add($permission)) {
                    throw new \RuntimeException("Could not create the '{$name}' permission.");
                }
            }

            if (!$auth->hasChild($admin, $permission) && !$auth->addChild($admin, $permission)) {
                throw new \RuntimeException("Could not grant '{$name}' to the admin role.");
            }
        }
    }

    public function safeDown(): bool
    {
        echo "Category permissions cannot be automatically removed without losing role assignments.\n";

        return false;
    }
}
