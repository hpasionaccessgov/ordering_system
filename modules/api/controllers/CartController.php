<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\services\ShoppingCart;
use yii\filters\AccessControl;

final class CartController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['index'], 'allow' => true, 'roles' => ['cart.index']],
                ['actions' => ['add'], 'allow' => true, 'roles' => ['cart.add']],
                ['actions' => ['update'], 'allow' => true, 'roles' => ['cart.update']],
                ['actions' => ['remove', 'clear'], 'allow' => true, 'roles' => ['cart.remove']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
            'add' => ['POST'],
            'update' => ['PATCH'],
            'remove' => ['DELETE'],
            'clear' => ['DELETE'],
        ];
    }

    public function actionIndex(): array
    {
        return $this->ok($this->serializeCart((new ShoppingCart())->summary()));
    }

    public function actionAdd(): array
    {
        $productId = $this->bodyInt('product_id', 1, PHP_INT_MAX);
        $quantity = $this->bodyInt('quantity', 1, ShoppingCart::MAX_QUANTITY);
        (new ShoppingCart())->add($productId, $quantity);

        return $this->ok($this->serializeCart((new ShoppingCart())->summary()));
    }

    public function actionUpdate(int $id): array
    {
        $quantity = $this->bodyInt('quantity', 0, ShoppingCart::MAX_QUANTITY);
        (new ShoppingCart())->update($id, $quantity);

        return $this->ok($this->serializeCart((new ShoppingCart())->summary()));
    }

    public function actionRemove(int $id): array
    {
        (new ShoppingCart())->remove($id);

        return $this->ok($this->serializeCart((new ShoppingCart())->summary()));
    }

    public function actionClear(): array
    {
        (new ShoppingCart())->clear();

        return $this->ok($this->serializeCart((new ShoppingCart())->summary()));
    }

    /**
     * @param array{lines: list<array{product: \app\models\Product, quantity: int, subtotal: string}>, total: string, count: int} $summary
     * @return array<string, mixed>
     */
    private function serializeCart(array $summary): array
    {
        $lines = array_map(
            static fn(array $line): array => [
                'product' => $line['product']->toArray(['id', 'name', 'price']),
                'quantity' => $line['quantity'],
                'subtotal' => $line['subtotal'],
            ],
            $summary['lines'],
        );

        return ['lines' => $lines, 'total' => $summary['total'], 'count' => $summary['count']];
    }
}
