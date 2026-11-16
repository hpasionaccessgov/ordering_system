<?php

declare(strict_types=1);

namespace app\services;

use yii\db\ActiveQuery;

final class OrderAccessPolicy
{
    public static function canViewAllOrders(bool $isAdmin, bool $isStaff): bool
    {
        return $isAdmin || $isStaff;
    }

    public static function scopeToOwner(
        ActiveQuery $query,
        int|string $userId,
        bool $canViewAllOrders,
    ): ActiveQuery {
        if (!$canViewAllOrders) {
            $query->andWhere(['user_id' => $userId]);
        }

        return $query;
    }
}
