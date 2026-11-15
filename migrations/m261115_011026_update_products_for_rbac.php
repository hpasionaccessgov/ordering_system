<?php

use yii\db\Migration;

class m261115_011026_update_products_for_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-products-status',
            '{{%products}}',
            'status'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-products-status', '{{%products}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261115_011026_update_products_for_rbac cannot be reverted.\n";

        return false;
    }
    */
}
