<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class OrderStatusHistory extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%order_status_history}}';
    }

    public function rules(): array
    {
        return [
            [['order_id', 'to_status', 'created_at'], 'required'],
            [['order_id', 'changed_by', 'created_at'], 'integer'],
            [['from_status', 'to_status'], 'string', 'max' => 30],
            [['to_status'], 'in', 'range' => [
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PROCESSING,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ]],
        ];
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getChangedBy(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'changed_by']);
    }
}
