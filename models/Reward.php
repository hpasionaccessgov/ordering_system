<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Reward catalog item customers can redeem with loyalty points.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $point_cost
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class Reward extends ActiveRecord
{
    public const STATUS_ACTIVE = 10;
    public const STATUS_INACTIVE = 0;

    public static function tableName(): string
    {
        return '{{%reward}}';
    }

    public function behaviors(): array
    {
        return [TimestampBehavior::class];
    }

    public function rules(): array
    {
        return [
            [['name', 'point_cost'], 'required'],
            [['point_cost', 'status'], 'integer'],
            [['point_cost'], 'integer', 'min' => 1],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique'],
            [['description'], 'string'],
            [['description'], 'default', 'value' => null],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Reward',
            'description' => 'Description',
            'point_cost' => 'Points required',
            'status' => 'Status',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function getStatusLabel(): string
    {
        return $this->isActive() ? 'Active' : 'Disabled';
    }
}
