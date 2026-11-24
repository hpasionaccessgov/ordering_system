<?php

declare(strict_types=1);

namespace app\services;

use app\exceptions\InsufficientInventoryException;
use app\models\Inventory;
use app\models\InventoryTransaction;
use app\models\Order;
use app\models\Product;
use app\models\ProductIngredient;
use Yii;

final class ProductIngredientService
{
    /**
     * Replaces a product's recipe with the submitted rows.
     *
     * @param array<array{inventory_id?: mixed, quantity_required?: mixed}> $rows
     */
    public static function sync(Product $product, array $rows): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            ProductIngredient::deleteAll(['product_id' => $product->id]);

            $models = [];
            $seen = [];
            foreach ($rows as $index => $row) {
                $inventoryId = trim((string) ($row['inventory_id'] ?? ''));
                $quantity = trim((string) ($row['quantity_required'] ?? ''));
                if ($inventoryId === '' && $quantity === '') {
                    continue;
                }

                $model = new ProductIngredient([
                    'product_id' => (int) $product->id,
                    'inventory_id' => $inventoryId,
                    'quantity_required' => $quantity,
                ]);
                if (!$model->validate()) {
                    $product->addError('ingredients', 'Ingredient ' . ($index + 1) . ': ' . implode(' ', $model->getFirstErrors()));
                    $transaction->rollBack();

                    return false;
                }
                $inventoryKey = (int) $model->inventory_id;
                if (isset($seen[$inventoryKey])) {
                    $product->addError('ingredients', 'Ingredient ' . ($index + 1) . ': this inventory item is already part of the recipe.');
                    $transaction->rollBack();

                    return false;
                }

                $seen[$inventoryKey] = true;
                $models[] = $model;
            }

            foreach ($models as $model) {
                if (!$model->save(false)) {
                    $product->addError('ingredients', 'Ingredients could not be saved.');
                    $transaction->rollBack();

                    return false;
                }
            }

            $transaction->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Blocks completion when stock cannot cover every ingredient.
     */
    public static function validate(Order $order): void
    {
        $needs = self::requirements($order);
        if ($needs === []) {
            return;
        }

        $inventory = self::lockedInventory(array_keys($needs));
        $shortages = [];
        foreach ($needs as $inventoryId => $need) {
            $available = isset($inventory[$inventoryId])
                ? (int) $inventory[$inventoryId]->current_stock
                : 0;
            if ($available < $need['needed']) {
                $shortages[] = [
                    'name' => $need['name'],
                    'needed' => $need['needed'],
                    'available' => $available,
                ];
            }
        }

        if ($shortages !== []) {
            throw new InsufficientInventoryException($shortages);
        }
    }

    /**
     * Applies one DEDUCT movement per inventory item; call inside the order transaction.
     */
    public static function deduct(Order $order): void
    {
        $needs = self::requirements($order);
        if ($needs === []) {
            return;
        }

        $inventory = self::lockedInventory(array_keys($needs));
        $reference = sprintf('Order %s (ID %d)', $order->order_number, $order->id);
        foreach ($needs as $inventoryId => $need) {
            $item = $inventory[$inventoryId] ?? null;
            if ($item === null) {
                throw new \RuntimeException('An ingredient is no longer available.');
            }

            InventoryService::recordMovement(
                $item,
                -$need['needed'],
                InventoryTransaction::TYPE_DEDUCT,
                $reference,
                (int) $order->id,
            );
        }
    }

    /**
     * @return array<int, array{name: string, needed: int}>
     */
    private static function requirements(Order $order): array
    {
        $needs = [];
        $items = $order->getItems()
            ->with('product.productIngredients.inventory')
            ->all();

        foreach ($items as $item) {
            foreach ($item->product?->productIngredients ?? [] as $ingredient) {
                $inventoryId = (int) $ingredient->inventory_id;
                if (!isset($needs[$inventoryId])) {
                    $needs[$inventoryId] = [
                        'name' => $ingredient->inventory?->name ?? 'Inventory item #' . $inventoryId,
                        'needed' => 0,
                    ];
                }
                $needs[$inventoryId]['needed'] += (int) $ingredient->quantity_required * (int) $item->quantity;
            }
        }

        return $needs;
    }

    /**
     * @param list<int> $ids
     * @return array<int, Inventory>
     */
    private static function lockedInventory(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $names = [];
        $params = [];
        foreach (array_values($ids) as $index => $id) {
            $name = ':inventory' . $index;
            $names[] = $name;
            $params[$name] = $id;
        }
        Yii::$app->db->createCommand(
            'SELECT [[id]] FROM {{%inventory}} WHERE [[id]] IN (' . implode(', ', $names) . ') FOR UPDATE',
            $params,
        )->queryColumn();

        /** @var array<int, Inventory> $inventory */
        $inventory = Inventory::find()->where(['id' => $ids])->indexBy('id')->all();

        return $inventory;
    }
}
