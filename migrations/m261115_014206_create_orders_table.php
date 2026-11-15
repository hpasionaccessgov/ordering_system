<?php

use yii\db\Migration;

/**
 * Handles the creation of the `orders` table.
 */
class m261115_014206_create_orders_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%orders}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('pending'),
            'total_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-orders-user_id',
            '{{%orders}}',
            'user_id',
        );

        $this->createIndex(
            'idx-orders-status',
            '{{%orders}}',
            'status',
        );

        $this->addForeignKey(
            'fk-orders-user_id',
            '{{%orders}}',
            'user_id',
            '{{%users}}',
            'id',
            'RESTRICT',
            'CASCADE',
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey(
            'fk-orders-user_id',
            '{{%orders}}',
        );

        $this->dropTable('{{%orders}}');
    }
}