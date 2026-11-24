<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "inventory_transactions".
 *
 * @property int $id
 * @property int $inventory_id
 * @property int $quantity signed: positive for stock in, negative for deductions
 * @property string $transaction_type
 * @property string|null $remarks
 * @property int|null $created_by
 * @property int $created_at
 * @property int|null $order_id
 *
 * @property-read Inventory $inventory
 * @property-read User|null $createdBy
 * @property-read Order|null $order
 */
class InventoryTransaction extends ActiveRecord
{
    public const TYPE_STOCK_IN = 'STOCK_IN';
    public const TYPE_DEDUCT = 'DEDUCT';
    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';
    public const TYPE_WASTAGE = 'WASTAGE';

    public static function tableName(): string
    {
        return '{{%inventory_transactions}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public static function typeList(): array
    {
        return [
            self::TYPE_STOCK_IN => 'Stock in',
            self::TYPE_DEDUCT => 'Deduct',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_WASTAGE => 'Wastage',
        ];
    }

    public function rules(): array
    {
        return [
            [['inventory_id', 'quantity', 'transaction_type'], 'required'],
            [['inventory_id', 'quantity', 'created_by', 'order_id'], 'integer'],
            [['quantity'], 'compare', 'compareValue' => 0, 'operator' => '!='],
            [['transaction_type'], 'string', 'max' => 30],
            [['transaction_type'], 'in', 'range' => array_keys(self::typeList())],
            [['remarks'], 'string', 'max' => 500],
            [['inventory_id'], 'exist', 'skipOnError' => true, 'targetClass' => Inventory::class, 'targetAttribute' => ['inventory_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'inventory_id' => 'Item',
            'quantity' => 'Quantity',
            'transaction_type' => 'Transaction type',
            'remarks' => 'Remarks',
            'created_by' => 'Created by',
            'created_at' => 'Created at',
            'order_id' => 'Order',
        ];
    }

    public function getInventory(): ActiveQuery
    {
        return $this->hasOne(Inventory::class, ['id' => 'inventory_id']);
    }

    public function getCreatedBy(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getTypeLabel(): string
    {
        return self::typeList()[$this->transaction_type] ?? (string) $this->transaction_type;
    }
}
