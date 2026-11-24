<?php

declare(strict_types=1);

namespace app\services;

use app\models\Favorite;
use app\models\Product;
use Yii;
use yii\web\BadRequestHttpException;

final class FavoriteService
{
    /** @var array<int, array<int, int>> */
    private static array $idsByUser = [];

    /**
     * @return array<int, int> product ID => favorite ID
     */
    public static function ids(int $userId): array
    {
        if (!isset(self::$idsByUser[$userId])) {
            self::$idsByUser[$userId] = array_column(
                Favorite::find()
                    ->select(['id', 'product_id'])
                    ->where(['user_id' => $userId])
                    ->asArray()
                    ->all(),
                'id',
                'product_id',
            );
        }

        return self::$idsByUser[$userId];
    }

    public static function isFavorite(int $userId, int $productId): bool
    {
        return isset(self::ids($userId)[$productId]);
    }

    public static function add(int $userId, int $productId): bool
    {
        self::assertActiveProduct($productId);
        if (self::isFavorite($userId, $productId)) {
            return false;
        }

        $favorite = new Favorite(['user_id' => $userId, 'product_id' => $productId]);
        if (!$favorite->save()) {
            throw new BadRequestHttpException('This product could not be added to your favorites.');
        }

        self::$idsByUser[$userId][$productId] = (int) $favorite->id;

        return true;
    }

    public static function remove(int $userId, int $productId): bool
    {
        $removed = Favorite::deleteAll(['user_id' => $userId, 'product_id' => $productId]) > 0;
        if ($removed) {
            unset(self::$idsByUser[$userId][$productId]);
        }

        return $removed;
    }

    public static function countFor(int $userId): int
    {
        return (int) Favorite::find()->where(['user_id' => $userId])->count();
    }

    public static function latestProduct(int $userId): ?Product
    {
        $favorite = Favorite::find()
            ->where(['user_id' => $userId])
            ->with('product')
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->one();
        $product = $favorite?->product;

        return $product !== null && $product->isActive() ? $product : null;
    }

    private static function assertActiveProduct(int $productId): void
    {
        if (!Product::find()->where(['id' => $productId, 'status' => Product::STATUS_ACTIVE])->exists()) {
            throw new BadRequestHttpException('This product is unavailable and cannot be favorited.');
        }
    }
}
