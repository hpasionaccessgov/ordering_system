<?php

use yii\db\Migration;

class m261120_100000_create_activity_log extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%activity_log}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->null(),
            'event' => $this->string(20)->notNull(),
            'route' => $this->string(255)->null(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-activity_log-user', '{{%activity_log}}', 'user_id');
        $this->createIndex('idx-activity_log-event-id', '{{%activity_log}}', ['event', 'id']);
        $this->addForeignKey('fk-activity_log-user', '{{%activity_log}}', 'user_id', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%activity_log}}');
    }
}
