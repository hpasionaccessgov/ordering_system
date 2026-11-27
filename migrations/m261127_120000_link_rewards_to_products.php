<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Links each reward to the menu product a redemption fulfils.
 */
class m261127_120000_link_rewards_to_products extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%reward}}', 'product_id', $this->integer()->null()->after('point_cost'));
        $this->createIndex('idx-reward-product', '{{%reward}}', ['product_id']);
        $this->addForeignKey(
            'fk-reward-product_id',
            '{{%reward}}',
            'product_id',
            '{{%products}}',
            'id',
            'SET NULL',
            'CASCADE',
        );

        $this->mapReward('Free Coffee', 'Cafebara House Latte');
        $this->mapReward('Free Dessert', 'Butter Croissant');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-reward-product_id', '{{%reward}}');
        $this->dropIndex('idx-reward-product', '{{%reward}}');
        $this->dropColumn('{{%reward}}', 'product_id');
    }

    private function mapReward(string $rewardName, string $productName): void
    {
        $productId = (new \yii\db\Query())
            ->select('id')
            ->from('{{%products}}')
            ->where(['name' => $productName])
            ->scalar($this->db);

        if ($productId === false || $productId === null) {
            return;
        }

        $this->update('{{%reward}}', ['product_id' => (int) $productId], ['name' => $rewardName]);
    }
}
