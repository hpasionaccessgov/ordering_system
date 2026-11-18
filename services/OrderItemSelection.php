<?php

declare(strict_types=1);

namespace app\services;

use yii\web\BadRequestHttpException;

final class OrderItemSelection
{
    public const MAX_QUANTITY = 99;

    /**
     * @param array<mixed> $requestedItems
     * @return list<array{product_id: int, quantity: int}>
     */
    public static function selectedItems(array $requestedItems): array
    {
        $selectedItems = [];

        foreach ($requestedItems as $requestedItem) {
            if (!is_array($requestedItem)) {
                throw new BadRequestHttpException('One or more selected products are invalid.');
            }

            $productId = filter_var($requestedItem['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($requestedItem['quantity'] ?? null, FILTER_VALIDATE_INT);

            if ($productId === false || $productId < 1 || $quantity === false || $quantity < 0) {
                throw new BadRequestHttpException('One or more selected products are invalid.');
            }

            if ($quantity === 0) {
                continue;
            }

            if ($quantity > self::MAX_QUANTITY) {
                throw new BadRequestHttpException(
                    'Quantity cannot exceed ' . self::MAX_QUANTITY . ' per product.',
                );
            }

            $combinedQuantity = ($selectedItems[$productId]['quantity'] ?? 0) + $quantity;
            if ($combinedQuantity > self::MAX_QUANTITY) {
                throw new BadRequestHttpException(
                    'Quantity cannot exceed ' . self::MAX_QUANTITY . ' per product.',
                );
            }
            $selectedItems[$productId] = [
                'product_id' => $productId,
                'quantity' => $combinedQuantity,
            ];
        }

        if ($selectedItems === []) {
            throw new BadRequestHttpException('An order must contain at least one product.');
        }

        return array_values($selectedItems);
    }
}
