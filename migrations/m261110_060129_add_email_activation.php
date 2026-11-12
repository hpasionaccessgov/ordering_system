<?php

use yii\db\Migration;

class m261110_060129_add_email_activation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%Users}}',
            'activation_token_hash',
            $this->char(64)->null(),
        );

        $this->addColumn(
            '{{%users}}',
            'activation_token_expires_at',
            $this->integer()->null(),
        );

        $this->alterColumn(
            '{{%users}}',
            'status',
            $this->smallInteger()->notNull()->defaultValue(0),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'activation_token_expires_at');
        $this->dropColumn('{{%users}}', 'activation_token_hash');

        $this->alterColumn(
            '{{%users}}',
            'status',
            $this->smallInteger()->notNull()->defaultValue(10),
        );
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
