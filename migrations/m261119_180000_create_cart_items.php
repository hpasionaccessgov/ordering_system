<?php

use yii\db\Migration;

class m261119_180000_create_cart_items extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%cart_items}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'quantity' => $this->smallInteger()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            'idx-cart_items-user_product-unique',
            '{{%cart_items}}',
            ['user_id', 'product_id'],
            true,
        );
        $this->createIndex('idx-cart_items-product_id', '{{%cart_items}}', 'product_id');
        $this->addForeignKey(
            'fk-cart_items-user_id',
            '{{%cart_items}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-cart_items-product_id',
            '{{%cart_items}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%cart_items}}');
    }
}
