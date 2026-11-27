<?php

declare(strict_types=1);

namespace app\modules\api\models;

use app\services\OrderItemSelection;
use yii\base\Model;

final class OrderCreateForm extends Model
{
    public array $items = [];

    public function rules(): array
    {
        return [
            ['items', 'required'],
            ['items', 'validateItems'],
        ];
    }

    public function validateItems(string $attribute): void
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        if (!is_array($this->items) || $this->items === []) {
            $this->addError($attribute, 'An order must contain at least one product.');
            return;
        }

        foreach ($this->items as $index => $item) {
            if (!is_array($item)) {
                $this->addError($attribute, "Item #{$index} is invalid.");
                continue;
            }
            $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
            if (
                $productId === false || $productId < 1
                || $quantity === false || $quantity < 1 || $quantity > OrderItemSelection::MAX_QUANTITY
            ) {
                $this->addError($attribute, "Item #{$index} needs a valid product_id and quantity (1-99).");
            }
        }
    }

    /**
     * @return list<array{product_id: int, quantity: int}>
     */
    public function orderItems(): array
    {
        $items = [];
        foreach ($this->items as $item) {
            $items[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ];
        }

        return OrderItemSelection::selectedItems($items);
    }
}
