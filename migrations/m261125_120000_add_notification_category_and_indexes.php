<?php

declare(strict_types=1);

use yii\db\Migration;

class m261125_120000_add_notification_category_and_indexes extends Migration
{
    public function safeUp(): void
    {
        $table = '{{%notifications}}';
        $schema = $this->db->getTableSchema($table, true);
        if ($schema === null) {
            throw new RuntimeException('The notifications table is missing.');
        }

        if (!isset($schema->columns['category'])) {
            $this->addColumn($table, 'category', $this->string(20)->notNull()->defaultValue('system'));
            $schema = $this->db->getTableSchema($table, true);
        }

        $this->execute(
            "UPDATE {$table} SET category = CASE
                WHEN type LIKE 'inventory_%' THEN 'inventory'
                WHEN type LIKE 'report_%' THEN 'report'
                WHEN order_id IS NOT NULL OR type LIKE 'order_%' OR status IN ('pending','confirmed','processing','ready_for_pickup','completed','cancelled') THEN 'order'
                ELSE 'system'
            END",
        );

        $indexes = [];
        foreach ($this->db->schema->getTableIndexes($table, true) as $index) {
            $name = is_array($index) ? ($index['name'] ?? '') : ($index->name ?? '');
            if ($name !== '') {
                $indexes[$name] = $index;
            }
        }
        if (!isset($indexes['idx-notifications-user-category-read-created'])) {
            $this->createIndex(
                'idx-notifications-user-category-read-created',
                $table,
                ['user_id', 'category', 'read_at', 'created_at'],
            );
        }
    }

    public function safeDown(): bool
    {
        echo "Notification category cannot be safely removed without losing classification data.\n";

        return false;
    }
}
