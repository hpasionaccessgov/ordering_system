<?php

use yii\db\Migration;
use yii\db\ConstraintFinderInterface;

class m261120_170000_add_activity_log_retention extends Migration
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
        if (!isset($settings->columns['logs_expiration_days'])) {
            $this->addColumn('{{%application_settings}}', 'logs_expiration_days', $this->integer()->notNull()->defaultValue(90));
        }

        foreach ($schema->getTableIndexes('{{%activity_log}}', true) as $index) {
            if ($index->name === 'idx-activity_log-created_at') {
                if ($index->columnNames !== ['created_at']) {
                    throw new RuntimeException('The activity log retention index has incompatible columns.');
                }
                return;
            }
        }
        $this->createIndex('idx-activity_log-created_at', '{{%activity_log}}', 'created_at');
    }

    public function safeDown(): bool
    {
        echo "Log retention settings cannot be removed without losing configuration.\n";
        return false;
    }
}
