<?php

declare(strict_types=1);

namespace app\services;

use app\models\Order;
use app\models\OrderItem;
use app\models\Product;
use Yii;
use yii\db\IntegrityException;
use yii\web\BadRequestHttpException;

class OrderService
{
    /**
     * Creates a new order with the given user ID and requested items.
     *
     * @param int $userId The ID of the user creating the order.
     * @param array $requestedItems The items requested for the order.
     * @return Order The created order.
     * @throws BadRequestHttpException If the order cannot be created.
     */
    public function create(int $userId, array $requestedItems, ?string $checkoutToken = null): Order
    {
        if ($checkoutToken !== null) {
            $existing = Order::findOne(['checkout_token' => $checkoutToken]);
            if ($existing !== null) {
                if ((int) $existing->user_id !== $userId) {
                    throw new BadRequestHttpException('Checkout token is invalid.');
                }

                return $existing;
            }
        }

        $requestedItems = OrderItemSelection::selectedItems($requestedItems);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $products = $this->lockActiveProducts($requestedItems);
            $order = new Order([
                'user_id' => $userId,
                'order_number' => $this->newOrderNumber(),
                'checkout_token' => $checkoutToken,
                'status' => Order::STATUS_PENDING,
                'total_amount' => 0,
            ]);

            if (!$order->save()) {
                throw new BadRequestHttpException('Order could not be created.');
            }
            (new OrderStatusService())->recordCreation(
                $order,
                $userId,
            );

            $total = 0.0;

            foreach ($requestedItems as $requestedItem) {
                $productId = $requestedItem['product_id'];
                $quantity = $requestedItem['quantity'];

                if (!isset($products[$productId])) {
                    throw new BadRequestHttpException(
                        'One or more selected products are invalid.',
                    );
                }

                $product = $products[$productId];
                $unitPrice = (float) $product->price;
                $total += $unitPrice * $quantity;

                $item = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                    'subtotal' => number_format($unitPrice * $quantity, 2, '.', ''),
                ]);

                if (!$item->save()) {
                    throw new BadRequestHttpException(
                        'Order item could not be saved.',
                    );
                }
            }

            $order->total_amount = number_format($total, 2, '.', '');

            if (!$order->save(false, ['total_amount', 'updated_at'])) {
                throw new BadRequestHttpException(
                    'Order total could not be saved.',
                );
            }

            $transaction->commit();

            return $order;
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            if ($checkoutToken !== null && $exception instanceof IntegrityException) {
                $existing = Order::findOne(['checkout_token' => $checkoutToken]);
                if ($existing !== null && (int) $existing->user_id === $userId) {
                    return $existing;
                }
            }

            throw $exception;
        }
    }

    public function update(Order $order, array $requestedItems): Order
    {
        if (!$order->isEditableByCustomer()) {
            throw new BadRequestHttpException(
                'Only pending orders can be edited.',
            );
        }

        $requestedItems = OrderItemSelection::selectedItems($requestedItems);
        $productIds = array_column($requestedItems, 'product_id');

        $products = Product::find()
            ->where([
                'id' => $productIds,
                'status' => Product::STATUS_ACTIVE,
            ])
            ->indexBy('id')
            ->all();

        $transaction = Yii::$app->db->beginTransaction();

        try {
            OrderItem::deleteAll(['order_id' => $order->id]);

            $total = 0.0;

            foreach ($requestedItems as $requestedItem) {
                $productId = $requestedItem['product_id'];
                $quantity = $requestedItem['quantity'];

                if (!isset($products[$productId])) {
                    throw new BadRequestHttpException(
                        'One or more selected products are invalid.',
                    );
                }

                $product = $products[$productId];
                $unitPrice = (float) $product->price;
                $total += $unitPrice * $quantity;

                $item = new OrderItem([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                    'subtotal' => number_format($unitPrice * $quantity, 2, '.', ''),
                ]);

                if (!$item->save()) {
                    throw new BadRequestHttpException(
                        'Order item could not be saved.',
                    );
                }

            }

            $order->total_amount = number_format($total, 2, '.', '');
            if (!$order->save(false, ['total_amount', 'updated_at'])) {
                throw new BadRequestHttpException('Order total could not be saved.');
            }

            $transaction->commit();

            return $order;
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }
    }

    private function newOrderNumber(): string
    {
        return sprintf(
            'CB-%s-%s',
            date('Ymd'),
            strtoupper(bin2hex(random_bytes(4))),
        );
    }

    /**
     * @param list<array{product_id: int, quantity: int}> $requestedItems
     * @return array<int, Product>
     */
    private function lockActiveProducts(array $requestedItems): array
    {
        $productIds = array_column($requestedItems, 'product_id');
        $query = Product::find()
            ->select('id')
            ->where([
                'id' => $productIds,
                'status' => Product::STATUS_ACTIVE,
            ]);
        $lockedIds = Yii::$app->db->createCommand(
            $query->createCommand()->getRawSql() . ' FOR UPDATE',
        )->queryColumn();

        return Product::find()
            ->where(['id' => $lockedIds])
            ->indexBy('id')
            ->all();
    }
}
