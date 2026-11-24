<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Indexes used by report date grouping and transaction consumption queries.
 */
class m261124_060000_add_report_indexes extends Migration
{
    public function safeUp(): void
    {
        $this->createIndex('idx-orders-created_at', '{{%orders}}', 'created_at');
        $this->createIndex('idx-orders-status-created_at', '{{%orders}}', ['status', 'created_at']);
        $this->createIndex(
            'idx-inventory_transactions-type-created_at',
            '{{%inventory_transactions}}',
            ['transaction_type', 'created_at'],
        );
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-inventory_transactions-type-created_at', '{{%inventory_transactions}}');
        $this->dropIndex('idx-orders-status-created_at', '{{%orders}}');
        $this->dropIndex('idx-orders-created_at', '{{%orders}}');
    }
}
