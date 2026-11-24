<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Links stock deductions back to the order that caused them.
 */
class m261124_030000_add_order_reference_to_inventory_transactions extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%inventory_transactions}}',
            'order_id',
            $this->integer()->null()->after('transaction_type'),
        );
        $this->createIndex(
            '{{%idx-inventory_transactions-order_id}}',
            '{{%inventory_transactions}}',
            'order_id',
        );
        $this->addForeignKey(
            '{{%fk-inventory_transactions-order_id}}',
            '{{%inventory_transactions}}',
            'order_id',
            '{{%orders}}',
            'id',
            'SET NULL',
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('{{%fk-inventory_transactions-order_id}}', '{{%inventory_transactions}}');
        $this->dropIndex('{{%idx-inventory_transactions-order_id}}', '{{%inventory_transactions}}');
        $this->dropColumn('{{%inventory_transactions}}', 'order_id');
    }
}
