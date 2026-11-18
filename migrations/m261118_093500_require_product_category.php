<?php

use yii\db\Migration;

class m261118_093500_require_product_category extends Migration
{
    public function safeUp(): void
    {
        $this->alterColumn(
            '{{%products}}',
            'category_id',
            $this->integer()->notNull(),
        );
    }

    public function safeDown(): void
    {
        $this->alterColumn(
            '{{%products}}',
            'category_id',
            $this->integer()->null(),
        );
    }
}
