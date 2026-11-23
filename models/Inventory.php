<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "inventory".
 *
 * @property int $id
 * @property string $name
 * @property int $unit
 * @property int $current_stock
 * @property int $minimum_stock
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Inventory extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 10;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */

    public static function statusList()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public static function tableName()
    {
        return 'inventory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['minimum_stock'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => 10],
            [['name', 'unit', 'created_at', 'updated_at'], 'required'],
            [['unit', 'current_stock', 'minimum_stock', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'unit' => 'Unit',
            'current_stock' => 'Current Stock',
            'minimum_stock' => 'Minimum Stock',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     * @return InventoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new InventoryQuery(get_called_class());
    }
}
