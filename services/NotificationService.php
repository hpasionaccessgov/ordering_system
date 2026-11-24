<?php

declare(strict_types=1);

namespace app\services;

use app\models\Notification;
use app\models\User;
use Yii;

final class NotificationService
{
    public const TYPE_PRODUCT_CREATED = 'product_created';
    public const TYPE_PRODUCT_UPDATED = 'product_updated';
    public const TYPE_PRODUCT_ARCHIVED = 'product_archived';
    public const TYPE_PRODUCT_RESTORED = 'product_restored';
    public const TYPE_PRODUCT_DELETED = 'product_deleted';
    public const TYPE_CATEGORY_CREATED = 'category_created';
    public const TYPE_CATEGORY_UPDATED = 'category_updated';
    public const TYPE_CATEGORY_DELETED = 'category_deleted';
    public const TYPE_INVENTORY_LOW_STOCK = 'inventory_low_stock';
    public const TYPE_REVIEW_CREATED = 'review_created';
    public const TYPE_ORDER_REORDERED = 'order_reordered';

    public static function notifyAll(
        string $type,
        string $message,
        string $route,
        ?int $targetId = null,
    ): void {
        self::notify(self::recipientIds(false), $type, $message, $route, $targetId);
    }

    public static function notifyManagers(
        string $type,
        string $message,
        string $route,
        ?int $targetId = null,
    ): void {
        self::notify(self::recipientIds(true), $type, $message, $route, $targetId);
    }

    public static function notifyUser(
        int $userId,
        string $type,
        string $message,
        string $route,
        ?int $targetId = null,
    ): void {
        self::notify([$userId], $type, $message, $route, $targetId, false);
    }

    /**
     * @param list<int|string> $recipientIds
     */
    private static function notify(
        array $recipientIds,
        string $type,
        string $message,
        string $route,
        ?int $targetId,
        bool $excludeActor = true,
    ): void {
        $actorId = (int) Yii::$app->user->id;
        $rows = [];
        $createdAt = time();
        foreach ($recipientIds as $id) {
            if ($excludeActor && (int) $id === $actorId) {
                continue;
            }
            $rows[] = [$id, $type, $message, $route, $targetId, $createdAt];
        }
        if ($rows !== []) {
            Yii::$app->db->createCommand()->batchInsert(
                Notification::tableName(),
                ['user_id', 'type', 'message', 'target_route', 'target_id', 'created_at'],
                $rows,
            )->execute();
        }
    }

    /**
     * @return list<int|string>
     */
    private static function recipientIds(bool $managersOnly): array
    {
        $auth = Yii::$app->authManager;
        $ids = [];
        foreach (User::find()->select('id')->where(['status' => User::STATUS_ACTIVE])->column() as $id) {
            if (!$auth->checkAccess($id, 'notification.index')) {
                continue;
            }
            if (
                $managersOnly
                && !$auth->checkAccess($id, 'admin.access')
                && !$auth->checkAccess($id, 'staff.access')
            ) {
                continue;
            }
            $ids[] = $id;
        }

        return $ids;
    }
}
