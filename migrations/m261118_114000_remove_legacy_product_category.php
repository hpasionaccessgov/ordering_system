<?php

use yii\db\Migration;
use yii\db\Query;

class m261118_114000_remove_legacy_product_category extends Migration
{
    public function up(): void
    {
        $this->assertCategoryReferences();
        $products = $this->db->getTableSchema('{{%products}}', true);
        if (isset($products->columns['category'])) {
            $this->dropColumn('{{%products}}', 'category');
        }
    }

    public function down(): void
    {
        $this->assertCategoryReferences();
        $products = $this->db->getTableSchema('{{%products}}', true);
        if (!isset($products->columns['category'])) {
            $this->addColumn('{{%products}}', 'category', $this->string(150)->null());
        }

        $this->db->createCommand(
            'UPDATE {{%products}} p INNER JOIN {{%category}} c ON c.id = p.category_id SET p.category = c.name',
        )->execute();
        $this->alterColumn(
            '{{%products}}',
            'category',
            $this->string(150)->notNull()->defaultValue('Coffee'),
        );

        foreach ($this->db->schema->getTableIndexes('{{%products}}', true) as $index) {
            if ($index->name === 'idx-products-category') {
                return;
            }
        }
        $this->createIndex('idx-products-category', '{{%products}}', 'category');
    }

    private function assertCategoryReferences(): void
    {
        $products = $this->db->getTableSchema('{{%products}}', true);
        $categories = $this->db->getTableSchema('{{%category}}', true);
        if (
            $products === null
            || $categories === null
            || !isset($products->columns['category_id'])
            || $products->columns['category_id']->allowNull
        ) {
            throw new RuntimeException('Products must have a non-null category_id before removing the legacy column.');
        }

        $hasForeignKey = false;
        foreach ($products->foreignKeys as $foreignKey) {
            if (
                $foreignKey[0] === $categories->fullName
                && ($foreignKey['category_id'] ?? null) === 'id'
            ) {
                $hasForeignKey = true;
                break;
            }
        }
        if (!$hasForeignKey) {
            throw new RuntimeException('The products category_id foreign key is missing.');
        }

        $invalidCount = (new Query())
            ->from(['p' => '{{%products}}'])
            ->leftJoin(['c' => '{{%category}}'], '[[c.id]] = [[p.category_id]]')
            ->where(['c.id' => null])
            ->count('*', $this->db);
        if ((int) $invalidCount !== 0) {
            throw new RuntimeException('Products contain invalid category references; repair them before continuing.');
        }
    }
}
