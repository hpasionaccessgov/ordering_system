<?php

use yii\db\Migration;

class m261120_150000_add_activity_notification_permissions extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $permissions = [];
        foreach (['activityLog.index', 'activityLog.view', 'notification.index', 'notification.read'] as $name) {
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

        foreach ($auth->getRoles() as $role) {
            $names = ['notification.index', 'notification.read'];
            if (isset($auth->getPermissionsByRole($role->name)['admin.access'])) {
                $names[] = 'activityLog.index';
                $names[] = 'activityLog.view';
            }
            foreach ($names as $name) {
                if (!$auth->hasChild($role, $permissions[$name]) && !$auth->addChild($role, $permissions[$name])) {
                    throw new RuntimeException("Could not grant '{$name}' to '{$role->name}'.");
                }
            }
        }
    }

    public function safeDown(): bool
    {
        echo "Activity and notification permissions cannot be removed without losing role assignments.\n";

        return false;
    }
}
