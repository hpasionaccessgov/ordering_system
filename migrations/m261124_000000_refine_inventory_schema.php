<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Fills in the gaps left by the generated inventory scaffold.
 */
class m261124_000000_refine_inventory_schema extends Migration
{
    public function safeUp(): void
    {
        $this->alterColumn('{{%inventory}}', 'unit', $this->string(50)->notNull());
        $this->addColumn('{{%inventory}}', 'description', $this->text()->null()->after('name'));
        $this->addColumn('{{%inventory}}', 'low_stock_notified', $this->boolean()->notNull()->defaultValue(0));

        $this->createTable('{{%inventory_transactions}}', [
            'id' => $this->primaryKey(),
            'inventory_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull(),
            'transaction_type' => $this->string(30)->notNull(),
            'remarks' => $this->string(500)->null(),
            'created_by' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            '{{%idx-inventory_transactions-inventory_id}}',
            '{{%inventory_transactions}}',
            'inventory_id',
        );
        $this->addForeignKey(
            '{{%fk-inventory_transactions-inventory_id}}',
            '{{%inventory_transactions}}',
            'inventory_id',
            '{{%inventory}}',
            'id',
            'CASCADE',
        );
        $this->addForeignKey(
            '{{%fk-inventory_transactions-created_by}}',
            '{{%inventory_transactions}}',
            'created_by',
            '{{%users}}',
            'id',
            'SET NULL',
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('{{%fk-inventory_transactions-created_by}}', '{{%inventory_transactions}}');
        $this->dropForeignKey('{{%fk-inventory_transactions-inventory_id}}', '{{%inventory_transactions}}');
        $this->dropTable('{{%inventory_transactions}}');
        $this->dropColumn('{{%inventory}}', 'low_stock_notified');
        $this->dropColumn('{{%inventory}}', 'description');
        $this->alterColumn('{{%inventory}}', 'unit', $this->integer(50)->notNull());
    }
}
