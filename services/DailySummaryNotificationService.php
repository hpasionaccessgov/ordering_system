<?php

declare(strict_types=1);

namespace app\services;

use app\models\Notification;
use app\models\Order;
use Yii;

final class DailySummaryNotificationService
{
    public static function createIfMissing(?int $dayStart = null): int
    {
        $tz = new \DateTimeZone((string) (Yii::$app->formatter->timeZone ?? 'Asia/Manila'));
        $now = new \DateTimeImmutable('now', $tz);
        $day = $dayStart !== null ? (new \DateTimeImmutable('@' . $dayStart))->setTimezone($tz) : $now;
        $start = $day->setTime(0, 0, 0)->getTimestamp();
        $end = $day->setTime(23, 59, 59)->getTimestamp();

        $existing = Notification::find()
            ->where(['type' => NotificationService::TYPE_REPORT_DAILY_SUMMARY])
            ->andWhere(['between', 'created_at', $start, $end])
            ->exists();
        if ($existing) {
            return 0;
        }

        $orders = (int) Order::find()->where(['between', 'created_at', $start, $end])->count();
        $completed = (int) Order::find()->where(['status' => Order::STATUS_COMPLETED])->andWhere(['between', 'created_at', $start, $end])->count();
        $cancelled = (int) Order::find()->where(['status' => Order::STATUS_CANCELLED])->andWhere(['between', 'created_at', $start, $end])->count();
        $revenue = (float) (Order::find()
            ->where(['status' => Order::STATUS_COMPLETED])
            ->andWhere(['between', 'created_at', $start, $end])
            ->sum('total_amount') ?? 0);
        $lowStock = (int) \app\models\Inventory::find()
            ->where(['status' => \app\models\Inventory::STATUS_ACTIVE])
            ->andWhere('current_stock <= minimum_stock')
            ->count();

        $message = sprintf(
            "Daily Summary: Today's Orders: %d | Revenue: %s | Completed Orders: %d | Cancelled Orders: %d | Low Stock Items: %d",
            $orders,
            Yii::$app->formatter->asCurrency($revenue),
            $completed,
            $cancelled,
            $lowStock,
        );

        NotificationService::notifyAdmins(
            NotificationService::TYPE_REPORT_DAILY_SUMMARY,
            $message,
            '/reports/sales',
            null,
            Notification::CATEGORY_REPORT,
            false,
        );

        return 1;
    }
}
