<?php

declare(strict_types=1);

namespace app\services;

use app\exceptions\InsufficientInventoryException;
use app\models\Order;
use app\models\Product;
use Yii;
use yii\web\BadRequestHttpException;

final class ReorderService
{
    /**
     * Adds still-available items from a completed order to the customer's cart.
     *
     * @return array{added: int, skipped: list<string>}
     */
    public function reorder(int $userId, int $orderId): array
    {
        $order = Order::find()
            ->where([
                'id' => $orderId,
                'user_id' => $userId,
                'status' => Order::STATUS_COMPLETED,
            ])
            ->with('items')
            ->one();
        if ($order === null) {
            throw new BadRequestHttpException('Only your completed orders can be reordered.');
        }

        $cart = new ShoppingCart();
        $current = $cart->items();
        $activeIds = array_map('intval', Product::find()
            ->select('id')
            ->where(['status' => Product::STATUS_ACTIVE])
            ->column());
        $current = array_intersect_key($current, array_flip($activeIds));

        $addable = [];
        $merged = $current;
        $skipped = [];
        foreach ($order->items as $item) {
            $productId = (int) $item->product_id;
            if (!in_array($productId, $activeIds, true)) {
                $skipped[] = (string) $item->product_name;
                continue;
            }

            $before = $merged[$productId] ?? 0;
            $after = min(ShoppingCart::MAX_QUANTITY, $before + (int) $item->quantity);
            if ($after === $before) {
                $skipped[] = (string) $item->product_name . ' (cart limit reached)';
                continue;
            }

            $merged[$productId] = $after;
            $addable[] = ['product_id' => $productId, 'quantity' => $after - $before];
        }

        if ($addable === []) {
            throw new BadRequestHttpException('No items from this order are still available to add.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            ProductIngredientService::validateQuantities($merged);
            foreach ($addable as $item) {
                $cart->add($item['product_id'], $item['quantity']);
            }
            NotificationService::notifyUser(
                $userId,
                NotificationService::TYPE_ORDER_REORDERED,
                "Items from order {$order->order_number} were added to your cart.",
                '/cart/index',
            );
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            if ($exception instanceof InsufficientInventoryException) {
                throw new BadRequestHttpException(str_replace(
                    'Cannot complete order.',
                    'Cannot add this order to your cart.',
                    $exception->getMessage(),
                ));
            }

            throw $exception;
        }

        return ['added' => count($addable), 'skipped' => $skipped];
    }
}
