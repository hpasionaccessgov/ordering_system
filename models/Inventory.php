<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "inventory".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $unit
 * @property int $current_stock
 * @property int $minimum_stock
 * @property int $status
 * @property int $low_stock_notified
 * @property int $created_at
 * @property int $updated_at
 *
 * @property-read InventoryTransaction[] $transactions
 * @property-read ProductIngredient[] $productIngredients
 * @property-read Product[] $products
 */
class Inventory extends ActiveRecord
{
    public const STATUS_ACTIVE = 10;
    public const STATUS_INACTIVE = 0;

    public static function tableName(): string
    {
        return '{{%inventory}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function rules(): array
    {
        return [
            [['name', 'unit'], 'required'],
            [['current_stock', 'minimum_stock'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['current_stock', 'minimum_stock'], 'number', 'integerOnly' => true, 'min' => 0],
            [['status'], 'integer'],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            [['name'], 'string', 'max' => 255],
            [['unit'], 'string', 'max' => 50],
            [['description'], 'string'],
            [['description'], 'default', 'value' => null],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Item',
            'description' => 'Description',
            'unit' => 'Unit',
            'current_stock' => 'Current stock',
            'minimum_stock' => 'Minimum stock',
            'status' => 'Status',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ];
    }

    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(InventoryTransaction::class, ['inventory_id' => 'id'])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public function getProductIngredients(): ActiveQuery
    {
        return $this->hasMany(ProductIngredient::class, ['inventory_id' => 'id']);
    }

    public function getProducts(): ActiveQuery
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('productIngredients');
    }

    public function isLowStock(): bool
    {
        return (int) $this->current_stock < (int) $this->minimum_stock;
    }

    public function isOutOfStock(): bool
    {
        return (int) $this->current_stock <= 0;
    }

    public function getStatusLabel(): string
    {
        return self::statusList()[(int) $this->status] ?? 'Inactive';
    }

    public static function find(): InventoryQuery
    {
        return new InventoryQuery(get_called_class());
    }
}
