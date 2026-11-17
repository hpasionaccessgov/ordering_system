<?php

use yii\db\Migration;

class m261115_180000_enhance_products_catalog extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%products}}', 'category', $this->string(80)->notNull()->defaultValue('Coffee'));
        $this->addColumn('{{%products}}', 'image_path', $this->string(255)->null());
        $this->addColumn('{{%products}}', 'is_featured', $this->boolean()->notNull()->defaultValue(false));
        $this->createIndex('idx-products-category', '{{%products}}', 'category');
        $this->createIndex('idx-products-featured', '{{%products}}', 'is_featured');
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-products-featured', '{{%products}}');
        $this->dropIndex('idx-products-category', '{{%products}}');
        $this->dropColumn('{{%products}}', 'is_featured');
        $this->dropColumn('{{%products}}', 'image_path');
        $this->dropColumn('{{%products}}', 'category');
    }
}
