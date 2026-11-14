<?php

use yii\db\Migration;

class m261111_154500_remove_pending_registration_requirement extends Migration
{
    public function safeUp(): void
    {
        $this->update(
            '{{%users}}',
            [
                'status' => 10,
                'activation_token_hash' => null,
                'activation_token_expires_at' => null,
            ],
            ['status' => 30],
        );

        $this->alterColumn(
            '{{%users}}',
            'status',
            $this->smallInteger()->notNull()->defaultValue(10),
        );
    }

    public function safeDown()
    {
        echo "This migration cannot be reverted because pending users were activated and their activation tokens were cleared.\n";

        return false;
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
