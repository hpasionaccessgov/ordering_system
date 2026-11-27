<?php

declare(strict_types=1);

namespace app\services;

use app\models\Product;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Session;

final class ShoppingCart
{
    public const MAX_QUANTITY = 99;
    private const SESSION_KEY = 'shopping-cart';

    public function add(int $productId, int $quantity): void
    {
        $quantity = $this->normalizeQuantity($quantity);
        $this->assertActiveProduct($productId);
        $items = $this->items();
        $items[$productId] = min(
            self::MAX_QUANTITY,
            ($items[$productId] ?? 0) + $quantity,
        );
        $this->storeItems($items);
    }

    public function update(int $productId, int $quantity): void
    {
        $quantity = $this->normalizeQuantity($quantity, true);
        $items = $this->items();
        if (!isset($items[$productId])) {
            throw new BadRequestHttpException('This product is not in your cart.');
        }
        if ($quantity === 0) {
            unset($items[$productId]);
        } else {
            $this->assertActiveProduct($productId);
            $items[$productId] = $quantity;
        }
        $this->storeItems($items);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);
        $this->storeItems($items);
    }

    public function clear(): void
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->db->createCommand()
                ->delete('{{%cart_items}}', ['user_id' => Yii::$app->user->id])
                ->execute();
        }
        $this->session()->remove(self::SESSION_KEY);
    }

    public function removeUnavailable(): int
    {
        $items = $this->items();
        if ($items === []) {
            return 0;
        }

        $activeIds = Product::find()
            ->select('id')
            ->where(['id' => array_keys($items), 'status' => Product::STATUS_ACTIVE])
            ->column();
        $activeIds = array_flip(array_map('intval', $activeIds));
        $availableItems = array_filter(
            $items,
            static fn(int $productId): bool => isset($activeIds[$productId]),
            ARRAY_FILTER_USE_KEY,
        );
        $removed = count($items) - count($availableItems);
        if ($removed > 0) {
            $this->storeItems($availableItems);
        }

        return $removed;
    }

    /**
     * @return array<int, int>
     */
    public function items(): array
    {
        $items = Yii::$app->user->isGuest
            ? $this->sessionItems()
            : array_column(
                (new \yii\db\Query())
                    ->select(['product_id', 'quantity'])
                    ->from('{{%cart_items}}')
                    ->where(['user_id' => Yii::$app->user->id])
                    ->all(),
                'quantity',
                'product_id',
            );

        $validated = [];
        foreach ($items as $productId => $quantity) {
            if (
                filter_var($productId, FILTER_VALIDATE_INT) !== false
                && filter_var($quantity, FILTER_VALIDATE_INT) !== false
                && (int) $productId > 0
                && (int) $quantity > 0
                && (int) $quantity <= self::MAX_QUANTITY
            ) {
                $validated[(int) $productId] = (int) $quantity;
            }
        }

        return $validated;
    }

    /**
     * @return array{lines: list<array{product: Product, quantity: int, subtotal: string}>, total: string, count: int}
     */
    public function summary(): array
    {
        $items = $this->items();
        $products = Product::find()
            ->where(['id' => array_keys($items), 'status' => Product::STATUS_ACTIVE])
            ->indexBy('id')
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $lines = [];
        $total = 0.0;
        $count = 0;
        foreach ($products as $product) {
            $quantity = $items[(int) $product->id] ?? 0;
            if ($quantity === 0) {
                continue;
            }
            $subtotal = (float) $product->price * $quantity;
            $total += $subtotal;
            $count += $quantity;
            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => number_format($subtotal, 2, '.', ''),
            ];
        }

        return [
            'lines' => $lines,
            'total' => number_format($total, 2, '.', ''),
            'count' => $count,
        ];
    }

    public function checkoutToken(): string
    {
        $stored = $this->session()->get(self::SESSION_KEY, []);
        $token = is_array($stored) ? ($stored['checkout_token'] ?? null) : null;
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(24));
            $this->storeToken($token);
        }

        return $token;
    }

    public function assertCheckoutToken(string $token): void
    {
        if ($token === '' || !hash_equals($this->checkoutToken(), $token)) {
            throw new BadRequestHttpException('Your checkout session expired. Review your cart and try again.');
        }
    }

    private function normalizeQuantity(int $quantity, bool $allowZero = false): int
    {
        $minimum = $allowZero ? 0 : 1;
        if ($quantity < $minimum || $quantity > self::MAX_QUANTITY) {
            throw new BadRequestHttpException(
                "Quantity must be between {$minimum} and " . self::MAX_QUANTITY . '.',
            );
        }

        return $quantity;
    }

    private function assertActiveProduct(int $productId): void
    {
        if (!Product::find()->where(['id' => $productId, 'status' => Product::STATUS_ACTIVE])->exists()) {
            throw new BadRequestHttpException('This product is unavailable and cannot be added.');
        }
    }

    /**
     * @param array<int, int> $items
     */
    private function storeItems(array $items, ?string $token = null): void
    {
        if (!Yii::$app->user->isGuest) {
            $this->storeDatabaseItems($items);
            if ($token !== null) {
                $this->storeToken($token);
            }

            return;
        }
        $this->session()->set(self::SESSION_KEY, [
            'items' => $items,
            'checkout_token' => $token ?? bin2hex(random_bytes(24)),
        ]);
    }

    public function mergeSessionIntoAccount(): void
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $sessionItems = $this->sessionItems();
        if ($sessionItems === []) {
            return;
        }
        $accountItems = $this->items();
        foreach ($sessionItems as $productId => $quantity) {
            $accountItems[$productId] = min(
                self::MAX_QUANTITY,
                ($accountItems[$productId] ?? 0) + $quantity,
            );
        }
        $this->storeItems($accountItems);
        $this->session()->remove(self::SESSION_KEY);
    }

    /**
     * @return array<int, int>
     */
    private function sessionItems(): array
    {
        $stored = $this->session()->get(self::SESSION_KEY, []);
        $items = is_array($stored) && is_array($stored['items'] ?? null)
            ? $stored['items']
            : [];

        return array_map('intval', $items);
    }

    /**
     * @param array<int, int> $items
     */
    private function storeDatabaseItems(array $items): void
    {
        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()
                ->delete('{{%cart_items}}', ['user_id' => Yii::$app->user->id])
                ->execute();
            $now = time();
            foreach ($items as $productId => $quantity) {
                $db->createCommand()->insert('{{%cart_items}}', [
                    'user_id' => Yii::$app->user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'created_by' => Yii::$app->user->id,
                    'updated_by' => Yii::$app->user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->execute();
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }
    }

    private function storeToken(string $token): void
    {
        $stored = $this->session()->get(self::SESSION_KEY, []);
        $this->session()->set(self::SESSION_KEY, [
            'items' => is_array($stored) ? ($stored['items'] ?? []) : [],
            'checkout_token' => $token,
        ]);
    }

    private function session(): Session
    {
        $session = Yii::$app->session;
        if (!$session->getIsActive()) {
            $session->open();
        }

        return $session;
    }
}
