<?php

use yii\db\Migration;

// phpcs:ignore Squiz.Classes.ValidClassName.NotPascalCase
class m261115_190000_store_product_images_in_database extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%products}}', 'image_data', 'MEDIUMBLOB NULL');
        $this->addColumn('{{%products}}', 'image_mime', $this->string(100)->null());
        $this->addColumn('{{%products}}', 'image_name', $this->string(255)->null());
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%products}}', 'image_name');
        $this->dropColumn('{{%products}}', 'image_mime');
        $this->dropColumn('{{%products}}', 'image_data');
    }
}
