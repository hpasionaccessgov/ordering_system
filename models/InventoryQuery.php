<?php

declare(strict_types=1);

namespace app\models;

/**
 * This is the ActiveQuery class for [[Inventory]].
 *
 * @see Inventory
 */
class InventoryQuery extends \yii\db\ActiveQuery
{
    public function active(): self
    {
        return $this->andWhere(['status' => Inventory::STATUS_ACTIVE]);
    }

    public function lowStock(): self
    {
        return $this->active()->andWhere('[[current_stock]] <= [[minimum_stock]]');
    }

    public function outOfStock(): self
    {
        return $this->active()->andWhere(['<=', 'current_stock', 0]);
    }
}
