<?php

declare(strict_types=1);

namespace app\services;

use app\models\Order;
use app\models\OrderItem;
use app\models\Product;
use Yii;
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
    public function create(int $userId, array $requestedItems): Order
    {
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
            $order = new Order([
                'user_id' => $userId,
                'status' => Order::STATUS_PENDING,
                'total_amount' => 0,
            ]);

            if (!$order->save()) {
                throw new BadRequestHttpException('Order could not be created.');
            }

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
                    'quantity' => $quantity,
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
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
                    'quantity' => $quantity,
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
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
}
