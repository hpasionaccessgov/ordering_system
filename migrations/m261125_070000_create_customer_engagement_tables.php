<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Stores customer favorites and verified product reviews.
 */
class m261125_070000_create_customer_engagement_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%favorite}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('uq-favorite-user_product', '{{%favorite}}', ['user_id', 'product_id'], true);
        $this->createIndex('idx-favorite-product_id', '{{%favorite}}', 'product_id');
        $this->addForeignKey(
            'fk-favorite-user_id',
            '{{%favorite}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-favorite-product_id',
            '{{%favorite}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->createTable('{{%product_review}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'product_id' => $this->integer()->notNull(),
            'order_id' => $this->integer()->notNull(),
            'rating' => $this->tinyInteger()->notNull(),
            'review' => $this->text()->null(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            'uq-product_review-user_product_order',
            '{{%product_review}}',
            ['user_id', 'product_id', 'order_id'],
            true,
        );
        $this->createIndex('idx-product_review-product_rating', '{{%product_review}}', ['product_id', 'rating']);
        $this->addForeignKey(
            'fk-product_review-user_id',
            '{{%product_review}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-product_review-product_id',
            '{{%product_review}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'fk-product_review-order_id',
            '{{%product_review}}',
            'order_id',
            '{{%orders}}',
            'id',
            'RESTRICT',
            'CASCADE',
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%product_review}}');
        $this->dropTable('{{%favorite}}');
    }
}
