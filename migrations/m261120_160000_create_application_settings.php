<?php

use yii\db\Migration;

class m261120_160000_create_application_settings extends Migration
{
    public function safeUp(): void
    {
        $auth = Yii::$app->authManager;
        $permissions = [];
        foreach (['settings.index', 'settings.update'] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null && $auth->getRole($name) !== null) {
                throw new RuntimeException("Cannot create '{$name}': an RBAC role already uses that name.");
            }
        }

        $table = $this->db->schema->getTableSchema('{{%application_settings}}', true);
        if ($table === null) {
            $this->createTable('{{%application_settings}}', [
                'id' => $this->primaryKey(),
                'card_page_size' => $this->integer()->notNull()->defaultValue(6),
                'table_page_size' => $this->integer()->notNull()->defaultValue(9),
                'idle_minutes' => $this->integer()->notNull()->defaultValue(30),
                'login_max_attempts' => $this->integer()->notNull()->defaultValue(5),
                'login_reset_minutes' => $this->integer()->notNull()->defaultValue(1),
                'login_lock_minutes' => $this->integer()->notNull()->defaultValue(1),
            ]);
        } elseif (array_diff(
            ['id', 'card_page_size', 'table_page_size', 'idle_minutes', 'login_max_attempts', 'login_reset_minutes', 'login_lock_minutes'],
            array_keys($table->columns),
        ) !== []) {
            throw new RuntimeException('The existing application_settings table has an incompatible schema.');
        }
        if (!(new \yii\db\Query())->from('{{%application_settings}}')->where(['id' => 1])->exists($this->db)) {
            $this->insert('{{%application_settings}}', ['id' => 1]);
        }

        foreach (['settings.index', 'settings.update'] as $name) {
            $permission = $auth->getPermission($name);
            if ($permission === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $name;
                if (!$auth->add($permission)) {
                    throw new RuntimeException("Could not create '{$name}'.");
                }
            }
            $permissions[] = $permission;
        }
        foreach ($auth->getRoles() as $role) {
            if (!isset($auth->getPermissionsByRole($role->name)['admin.access'])) {
                continue;
            }
            foreach ($permissions as $permission) {
                if (!$auth->hasChild($role, $permission) && !$auth->addChild($role, $permission)) {
                    throw new RuntimeException("Could not grant '{$permission->name}' to '{$role->name}'.");
                }
            }
        }
    }

    public function safeDown(): bool
    {
        echo "Settings and permissions cannot be removed without losing configuration and role assignments.\n";
        return false;
    }
}
