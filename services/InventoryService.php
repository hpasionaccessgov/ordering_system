<?php

declare(strict_types=1);

namespace app\services;

use app\models\Inventory;
use app\models\InventoryTransaction;
use Yii;
use yii\helpers\Inflector;

final class InventoryService
{
    public static function create(Inventory $inventory): bool
    {
        return (bool) Yii::$app->db->transaction(static function () use ($inventory): bool {
            if (!$inventory->save()) {
                return false;
            }
            if ((int) $inventory->current_stock !== 0) {
                self::addTransaction($inventory, (int) $inventory->current_stock, InventoryTransaction::TYPE_STOCK_IN, 'Initial stock');
                self::notifyInventoryChange($inventory, NotificationService::TYPE_INVENTORY_STOCK_IN, 'Inventory Updated: ' . (int) $inventory->current_stock . ' ' . $inventory->unit . ' of ' . $inventory->name . ' added.');
            }
            self::syncLowStockNotification($inventory);

            return true;
        });
    }

    public static function update(Inventory $inventory): bool
    {
        $oldStock = (int) $inventory->getOldAttribute('current_stock');

        return (bool) Yii::$app->db->transaction(static function () use ($inventory, $oldStock): bool {
            if (!$inventory->save()) {
                return false;
            }
            $change = (int) $inventory->current_stock - $oldStock;
            if ($change !== 0) {
                $type = $change > 0 ? InventoryTransaction::TYPE_STOCK_IN : InventoryTransaction::TYPE_ADJUSTMENT;
                $remarks = $change > 0 ? 'Stock added' : 'Stock level updated';
                self::addTransaction($inventory, $change, $type, $remarks);
                $message = $change > 0
                    ? 'Inventory Updated: ' . abs($change) . ' ' . $inventory->unit . ' of ' . $inventory->name . ' added.'
                    : 'Inventory Adjusted: ' . $inventory->name . ' changed from ' . $oldStock . $inventory->unit . ' to ' . (int) $inventory->current_stock . $inventory->unit . '.';
                $notificationType = $change > 0
                    ? NotificationService::TYPE_INVENTORY_STOCK_IN
                    : NotificationService::TYPE_INVENTORY_ADJUSTED;
                self::notifyInventoryChange($inventory, $notificationType, $message);
            }
            self::syncLowStockNotification($inventory);

            return true;
        });
    }

    /**
     * Applies a signed quantity and records it. Call this from order/wastage flows.
     */
    public static function recordMovement(
        Inventory $inventory,
        int $quantity,
        string $type,
        ?string $remarks = null,
        ?int $orderId = null,
    ): bool {
        if ($quantity === 0) {
            throw new \InvalidArgumentException('A stock movement quantity cannot be zero.');
        }
        if (!array_key_exists($type, InventoryTransaction::typeList())) {
            throw new \InvalidArgumentException('Unknown inventory transaction type.');
        }

        return (bool) Yii::$app->db->transaction(static function () use ($inventory, $quantity, $type, $remarks, $orderId): bool {
            $newStock = (int) $inventory->current_stock + $quantity;
            if ($newStock < 0) {
                throw new \InvalidArgumentException('Stock cannot fall below zero.');
            }
            $inventory->updateAttributes(['current_stock' => $newStock, 'updated_at' => time()]);
            self::addTransaction($inventory, $quantity, $type, $remarks, $orderId);
            self::syncLowStockNotification($inventory);

            return true;
        });
    }

    public static function lowStockMessage(Inventory $inventory): string
    {
        // shortcut: simple pluralization; override per unit if a label reads awkwardly.
        return sprintf(
            'Low stock alert: %s has only %d %s remaining.',
            $inventory->name,
            (int) $inventory->current_stock,
            mb_strtolower(Inflector::pluralize($inventory->unit)),
        );
    }

    private static function addTransaction(
        Inventory $inventory,
        int $quantity,
        string $type,
        ?string $remarks,
        ?int $orderId = null,
    ): void {
        $transaction = new InventoryTransaction([
            'inventory_id' => (int) $inventory->id,
            'quantity' => $quantity,
            'transaction_type' => $type,
            'remarks' => $remarks,
            'created_by' => Yii::$app->has('user') ? Yii::$app->user->id : null,
            'order_id' => $orderId,
        ]);
        if (!$transaction->save()) {
            throw new \RuntimeException('Could not record the inventory transaction: ' . json_encode($transaction->errors));
        }
    }

    private static function syncLowStockNotification(Inventory $inventory): void
    {
        $notified = (int) $inventory->low_stock_notified === 1;

        if ($inventory->isLowStock() && !$notified) {
            NotificationService::notifyManagers(
                NotificationService::TYPE_INVENTORY_LOW_STOCK,
                self::lowStockMessage($inventory),
                '/sales/inventory',
                (int) $inventory->id,
                \app\models\Notification::CATEGORY_INVENTORY,
            );
            $inventory->updateAttributes(['low_stock_notified' => 1]);
        } elseif (!$inventory->isLowStock() && $notified) {
            $inventory->updateAttributes(['low_stock_notified' => 0]);
        }
    }

    private static function notifyInventoryChange(Inventory $inventory, string $type, string $message): void
    {
        NotificationService::notifyManagers(
            $type,
            $message,
            '/sales/inventory',
            (int) $inventory->id,
            \app\models\Notification::CATEGORY_INVENTORY,
        );
    }
}
