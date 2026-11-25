<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class Notification extends ActiveRecord
{
    public const CATEGORY_ORDER = 'order';
    public const CATEGORY_INVENTORY = 'inventory';
    public const CATEGORY_SYSTEM = 'system';
    public const CATEGORY_REPORT = 'report';

    public static function tableName(): string
    {
        return '{{%notifications}}';
    }

    public static function categoryValues(): array
    {
        return [self::CATEGORY_ORDER, self::CATEGORY_INVENTORY, self::CATEGORY_SYSTEM, self::CATEGORY_REPORT];
    }

    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_ORDER => 'Order',
            self::CATEGORY_INVENTORY => 'Inventory',
            self::CATEGORY_SYSTEM => 'System',
            self::CATEGORY_REPORT => 'Report',
        ];
    }

    public static function categoryLabel(string $category): string
    {
        return self::categoryLabels()[$category] ?? ucfirst($category);
    }

    public static function categoryBadgeClass(string $category): string
    {
        return match ($category) {
            self::CATEGORY_ORDER => 'text-bg-primary',
            self::CATEGORY_INVENTORY => 'text-bg-warning',
            self::CATEGORY_REPORT => 'text-bg-success',
            default => 'text-bg-secondary',
        };
    }

    public static function categoryIconClass(string $category): string
    {
        return match ($category) {
            self::CATEGORY_ORDER => 'bi-cart-check',
            self::CATEGORY_INVENTORY => 'bi-box-seam',
            self::CATEGORY_REPORT => 'bi-graph-up',
            default => 'bi-gear',
        };
    }

    public static function categoryForType(?string $type): string
    {
        $type = (string) $type;
        return match (true) {
            str_starts_with($type, 'inventory_') => self::CATEGORY_INVENTORY,
            str_starts_with($type, 'report_') => self::CATEGORY_REPORT,
            str_starts_with($type, 'order_') => self::CATEGORY_ORDER,
            str_starts_with($type, 'product_'),
            str_starts_with($type, 'category_'),
            str_starts_with($type, 'review_'),
            str_starts_with($type, 'system_') => self::CATEGORY_SYSTEM,
            default => self::CATEGORY_SYSTEM,
        };
    }

    public function getCategoryLabel(): string
    {
        return self::categoryLabel($this->resolvedCategory());
    }

    public function getCategoryBadgeClass(): string
    {
        return self::categoryBadgeClass($this->resolvedCategory());
    }

    public function getCategoryIconClass(): string
    {
        return self::categoryIconClass($this->resolvedCategory());
    }

    public function resolvedCategory(): string
    {
        $category = (string) ($this->category ?? '');
        if (in_array($category, self::categoryValues(), true)) {
            return $category;
        }
        if ($this->order_id !== null) {
            return self::CATEGORY_ORDER;
        }

        return self::categoryForType($this->type);
    }

    public static function visibleTo(int|string $userId, bool $canViewAll): ActiveQuery
    {
        $query = self::find()->where(['user_id' => $userId]);
        if (!$canViewAll) {
            $query->andWhere([
                'or',
                ['order_id' => Order::find()->select('id')->where(['user_id' => $userId])],
                ['order_id' => null],
            ]);
        }

        return $query;
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getLabel(): string
    {
        if ($this->message !== null && $this->message !== '') {
            return (string) $this->message;
        }
        if ($this->order !== null) {
            $orderNumber = $this->order->order_number;
            return match ((string) $this->status) {
                Order::STATUS_PENDING => 'New order ' . $orderNumber . ' received.',
                Order::STATUS_READY_FOR_PICKUP => 'Your order ' . $orderNumber . ' is ready for pickup.',
                default => 'Order ' . $orderNumber . ' is ' . str_replace('_', ' ', (string) $this->status) . '.',
            };
        }

        $status = (string) ($this->status ?? $this->type ?? 'notification');
        return ucfirst(str_replace(['-', '_', '.'], ' ', $status)) . '.';
    }

    public function getTargetUrl(): array|string
    {
        $route = (string) $this->target_route;
        $targetId = $this->target_id;
        if (str_starts_with($route, '/') && !str_starts_with($route, '//')) {
            if ($targetId !== null) {
                return match ($route) {
                    '/sales/orders', '/order/view' => ['/sales/orders/' . (int) $targetId],
                    '/sales/cart', '/cart/index' => ['/sales/cart'],
                    '/sales/inventory', '/inventory/view' => ['/sales/inventory/' . (int) $targetId],
                    '/reports/sales' => ['/reports/sales'],
                    default => [$route, 'id' => $targetId],
                };
            }
            return [$route];
        }
        if ($this->order !== null && Yii::$app->user->can('order.view')) {
            return ['/sales/orders/' . (int) $this->order->id];
        }

        return ['/notifications'];
    }
}
