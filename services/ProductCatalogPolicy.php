<?php

declare(strict_types=1);

namespace app\services;

use app\models\Product;
use yii\db\ActiveQuery;

final class ProductCatalogPolicy
{
    public static function normalizeStatus(bool $canManageProducts, string $requestedStatus): string
    {
        if (!$canManageProducts) {
            return 'active';
        }

        return in_array($requestedStatus, ['all', 'active', 'archived'], true)
            ? $requestedStatus
            : 'all';
    }

    public static function applyStatusFilter(ActiveQuery $query, string $status): ActiveQuery
    {
        if ($status === 'active') {
            $query->andWhere(['status' => Product::STATUS_ACTIVE]);
        } elseif ($status === 'archived') {
            $query->andWhere(['status' => Product::STATUS_ARCHIVED]);
        }

        return $query;
    }
}
