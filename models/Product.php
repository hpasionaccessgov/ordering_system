<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Product extends ActiveRecord
{

    public const STATUS_ARCHIVED = 0;
    public const STATUS_ACTIVE = 10;
    public static function tableName(): string
    {
        return '{{%products}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['name', 'price', 'status'], 'required'],
            [['description'], 'string'],
            [['price'], 'number', 'min' => 0, 'max' => 99999999.99],
            [['price'], 'filter', 'filter' => static function ($value): string {
                return number_format((float) $value, 2, '.', '');
            }],
            [['status'], 'integer'],
            [
                ['status'],
                'in',
                'range' => [
                    self::STATUS_ACTIVE,
                    self::STATUS_ARCHIVED,
                ],
            ],
            [['name'], 'string', 'max' => 150],
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }
}
