<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%product_ingredient}}".
 *
 * @property int $id
 * @property int $product_id
 * @property int $inventory_id
 * @property int $quantity_required
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read Inventory $inventory
 * @property-read Product $product
 */
class ProductIngredient extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%product_ingredient}}';
    }

    public function behaviors(): array
    {
        return [
            BlameableBehavior::class,
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['product_id', 'inventory_id', 'quantity_required'], 'required'],
            [['product_id', 'inventory_id'], 'integer'],
            [['quantity_required'], 'number', 'integerOnly' => true, 'min' => 1],
            [
                ['product_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Product::class,
                'targetAttribute' => ['product_id' => 'id'],
            ],
            [
                ['inventory_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Inventory::class,
                'targetAttribute' => ['inventory_id' => 'id'],
            ],
            [
                ['inventory_id'],
                'unique',
                'targetAttribute' => ['product_id', 'inventory_id'],
                'message' => 'This inventory item is already part of the recipe.',
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product',
            'inventory_id' => 'Inventory item',
            'quantity_required' => 'Quantity required',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ];
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getInventory(): ActiveQuery
    {
        return $this->hasOne(Inventory::class, ['id' => 'inventory_id']);
    }
}
