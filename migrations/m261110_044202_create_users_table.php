<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */
class m261110_044202_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(59)->notNull(),
            'email' => $this->string(255)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'full_name' => $this->string(150)->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'auth_key' => $this->string(32)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');

        $this->createIndex(
            'uq-users-username',
            '{{%users}}',
            'username',
            true
        );
        $this->createIndex(
            'uq-users-email',
            '{{%users}}',
            'email',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%users}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261110_060129_add_email_activation cannot be reverted.\n";

        return false;
    }
    */
}
