<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class OrderNotification extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%order_notifications}}';
    }

    public static function visibleTo(int|string $userId, bool $canViewAll): ActiveQuery
    {
        $query = self::find()->where(['user_id' => $userId]);
        if (!$canViewAll) {
            $query->andWhere(['order_id' => Order::find()->select('id')->where(['user_id' => $userId])]);
        }

        return $query;
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }
}
