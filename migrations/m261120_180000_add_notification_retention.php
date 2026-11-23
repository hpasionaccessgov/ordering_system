<?php

use yii\db\ConstraintFinderInterface;
use yii\db\Migration;

class m261120_180000_add_notification_retention extends Migration
{
    public function safeUp(): void
    {
        $schema = $this->db->schema;
        if (!$schema instanceof ConstraintFinderInterface) {
            throw new RuntimeException('The database does not support index inspection.');
        }
        $settings = $schema->getTableSchema('{{%application_settings}}', true);
        if ($settings === null) {
            throw new RuntimeException('The application_settings table is missing.');
        }
        if (!isset($settings->columns['notifications_expiration_days'])) {
            $this->addColumn(
                '{{%application_settings}}',
                'notifications_expiration_days',
                $this->integer()->notNull()->defaultValue(30),
            );
        }

        // The index name stays unchanged when the table is generalized.
        $notificationTable = $schema->getTableSchema('{{%notifications}}', true) !== null
            ? '{{%notifications}}'
            : '{{%order_notifications}}';
        $hasIndex = false;
        foreach ($schema->getTableIndexes($notificationTable, true) as $index) {
            if ($index->name === 'idx-order_notifications-created_at') {
                if ($index->columnNames !== ['created_at']) {
                    throw new RuntimeException('The notification retention index has incompatible columns.');
                }
                $hasIndex = true;
                break;
            }
        }
        if (!$hasIndex) {
            $this->createIndex('idx-order_notifications-created_at', $notificationTable, 'created_at');
        }

        $auth = Yii::$app->authManager;
        $permission = $auth->getPermission('notification.delete');
        if ($permission === null) {
            if ($auth->getRole('notification.delete') !== null) {
                throw new RuntimeException("Cannot create 'notification.delete': an RBAC role already uses that name.");
            }
            $permission = $auth->createPermission('notification.delete');
            $permission->description = 'notification.delete';
            if (!$auth->add($permission)) {
                throw new RuntimeException("Could not create 'notification.delete'.");
            }
        }
        foreach ($auth->getRoles() as $role) {
            if (!isset($auth->getPermissionsByRole($role->name)['notification.index'])) {
                continue;
            }
            if (!$auth->hasChild($role, $permission) && !$auth->addChild($role, $permission)) {
                throw new RuntimeException("Could not grant 'notification.delete' to '{$role->name}'.");
            }
        }
    }

    public function safeDown(): bool
    {
        echo "Notification retention settings and permissions cannot be removed without losing configuration.\n";

        return false;
    }
}
