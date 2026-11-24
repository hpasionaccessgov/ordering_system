<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Recipes linking products to inventory items.
 */
class m261124_020000_create_product_ingredient_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%product_ingredient}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->notNull(),
            'inventory_id' => $this->integer()->notNull(),
            'quantity_required' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            '{{%idx-product_ingredient-product_inventory}}',
            '{{%product_ingredient}}',
            ['product_id', 'inventory_id'],
            true,
        );
        $this->createIndex(
            '{{%idx-product_ingredient-inventory_id}}',
            '{{%product_ingredient}}',
            'inventory_id',
        );
        $this->addForeignKey(
            '{{%fk-product_ingredient-product_id}}',
            '{{%product_ingredient}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE',
        );
        $this->addForeignKey(
            '{{%fk-product_ingredient-inventory_id}}',
            '{{%product_ingredient}}',
            'inventory_id',
            '{{%inventory}}',
            'id',
            'RESTRICT',
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('{{%fk-product_ingredient-inventory_id}}', '{{%product_ingredient}}');
        $this->dropForeignKey('{{%fk-product_ingredient-product_id}}', '{{%product_ingredient}}');
        $this->dropTable('{{%product_ingredient}}');
    }
}
