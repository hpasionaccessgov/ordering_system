<?php

use yii\db\Migration;

class m261127_140000_create_api_tokens extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%api_tokens}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token_hash' => $this->string(64)->notNull()->unique(),
            'expires_at' => $this->integer()->notNull(),
            'revoked_at' => $this->integer()->null(),
            'last_used_at' => $this->integer()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-api_tokens-user_id', '{{%api_tokens}}', 'user_id');
        $this->addForeignKey(
            'fk-api_tokens-user_id',
            '{{%api_tokens}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%api_tokens}}');
    }
}
