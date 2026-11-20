<?php

use yii\db\Migration;

class m261120_000000_create_order_notifications extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%order_notifications}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'order_id' => $this->integer()->notNull(),
            'status' => $this->string(30)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'read_at' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-order_notifications-user-read', '{{%order_notifications}}', ['user_id', 'read_at']);
        $this->createIndex('idx-order_notifications-order', '{{%order_notifications}}', 'order_id');
        $this->addForeignKey('fk-order_notifications-user', '{{%order_notifications}}', 'user_id', '{{%users}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-order_notifications-order', '{{%order_notifications}}', 'order_id', '{{%orders}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%order_notifications}}');
    }
}
