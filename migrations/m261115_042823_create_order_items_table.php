<?php

use yii\db\Migration;

/**
 * Handles the creation of the `order_items` table.
 */
class m261115_042823_create_order_items_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%order_items}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull(),
            'unit_price' => $this->decimal(10, 2)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-order_items-order_id',
            '{{%order_items}}',
            'order_id',
        );

        $this->createIndex(
            'idx-order_items-product_id',
            '{{%order_items}}',
            'product_id',
        );

        $this->addForeignKey(
            'fk-order_items-order_id',
            '{{%order_items}}',
            'order_id',
            '{{%orders}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        // Restrict product deletion. Products should be archived instead.
        $this->addForeignKey(
            'fk-order_items-product_id',
            '{{%order_items}}',
            'product_id',
            '{{%products}}',
            'id',
            'RESTRICT',
            'CASCADE',
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey(
            'fk-order_items-product_id',
            '{{%order_items}}',
        );

        $this->dropForeignKey(
            'fk-order_items-order_id',
            '{{%order_items}}',
        );

        $this->dropTable('{{%order_items}}');
    }
}