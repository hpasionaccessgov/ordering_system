<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%inventory}}`.
 */
class m261123_083800_create_inventory_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%inventory}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'unit' => $this->integer(50)->notNull(),
            'current_stock' => $this->integer()->notNull()->defaultValue(0),
            'minimum_stock' => $this->integer()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%inventory}}');
    }
}
