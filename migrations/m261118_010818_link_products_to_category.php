<?php

use yii\db\Migration;
use yii\db\Query;

class m261118_010818_link_products_to_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $names = (new Query())
            ->select('category')
            ->from('{{%products}}')
            ->distinct()
            ->column($this->db);

        $categoryIds = [];

        foreach ($names as $name) {
            if (!is_string($name) || trim($name) === '') {
                throw new RuntimeException('A product has an empty category.');
            }

            $matches = (new Query())
                ->select('id')
                ->from('{{%category}}')
                ->where(['name' => $name])
                ->limit(2)
                ->column($this->db);

            if (count($matches) !== 1) {
                throw new RuntimeException(
                    "Expected exactly one category matching: {$name}",
                );
            }

            $categoryIds[$name] = $matches[0];
        }

        $this->addColumn('{{%products}}', 'category_id', $this->integer()->null());

        foreach ($categoryIds as $name => $id) {
            $this->update(
                '{{%products}}',
                ['category_id' => $id],
                ['category' => $name],
            );
        }

        $this->createIndex(
            'idx-products-category_id',
            '{{%products}}',
            'category_id',
        );

        $this->addForeignKey(
            'fk-products-category_id',
            '{{%products}}',
            'category_id',
            '{{%category}}',
            'id',
            'RESTRICT',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-products-category_id', '{{%products}}');
        $this->dropIndex('idx-products-category_id', '{{%products}}');
        $this->dropColumn('{{%products}}', 'category_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m261118_010818_link_products_to_category cannot be reverted.\n";

        return false;
    }
    */
}
