<?php

declare(strict_types=1);

namespace app\services;

use app\models\Order;
use app\models\OrderItem;
use app\models\Product;
use app\models\ProductReview;
use Yii;
use yii\web\BadRequestHttpException;

final class ReviewService
{
    /**
     * Completed orders that contain the product and have not been reviewed for it yet.
     *
     * @return list<Order>
     */
    public static function eligibleOrders(int $userId, int $productId): array
    {
        return Order::find()
            ->alias('o')
            ->select('o.*')
            ->innerJoin(['oi' => '{{%order_items}}'], 'oi.order_id = o.id')
            ->leftJoin(
                ['pr' => '{{%product_review}}'],
                'pr.order_id = o.id AND pr.user_id = :user_id AND pr.product_id = :product_id',
                [':user_id' => $userId, ':product_id' => $productId],
            )
            ->where([
                'o.user_id' => $userId,
                'o.status' => Order::STATUS_COMPLETED,
                'oi.product_id' => $productId,
                'pr.id' => null,
            ])
            ->groupBy('o.id')
            ->orderBy(['o.created_at' => SORT_DESC, 'o.id' => SORT_DESC])
            ->all();
    }

    /**
     * @return list<ProductReview>
     */
    public static function forProduct(int $productId): array
    {
        return ProductReview::find()
            ->where(['product_id' => $productId])
            ->with('user')
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->all();
    }

    /**
     * @param list<int> $productIds
     * @return array<int, array{average: float, count: int}>
     */
    public static function summaries(array $productIds): array
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        if ($productIds === []) {
            return [];
        }

        $rows = ProductReview::find()
            ->select([
                'product_id',
                'average' => 'AVG(rating)',
                'count' => 'COUNT(*)',
            ])
            ->where(['product_id' => $productIds])
            ->groupBy('product_id')
            ->asArray()
            ->all();

        $summaries = [];
        foreach ($rows as $row) {
            $summaries[(int) $row['product_id']] = [
                'average' => round((float) $row['average'], 1),
                'count' => (int) $row['count'],
            ];
        }

        return $summaries;
    }

    public static function create(
        int $userId,
        int $productId,
        int $orderId,
        int $rating,
        string $review,
    ): ProductReview {
        if ($rating < 1 || $rating > 5) {
            throw new BadRequestHttpException('Rating must be between 1 and 5.');
        }

        $review = trim($review);
        if ($review === '' || mb_strlen($review) > 1000) {
            throw new BadRequestHttpException('Review must be between 1 and 1000 characters.');
        }

        $product = Product::find()->where(['id' => $productId])->one();
        if ($product === null) {
            throw new BadRequestHttpException('Product not found.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $order = Order::find()->where([
                'id' => $orderId,
                'user_id' => $userId,
                'status' => Order::STATUS_COMPLETED,
            ])->one();
            if ($order === null) {
                throw new BadRequestHttpException('You can only review products from your completed orders.');
            }
            if (!OrderItem::find()->where(['order_id' => $orderId, 'product_id' => $productId])->exists()) {
                throw new BadRequestHttpException('This product is not part of that order.');
            }
            if (ProductReview::find()->where([
                'user_id' => $userId,
                'product_id' => $productId,
                'order_id' => $orderId,
            ])->exists()) {
                throw new BadRequestHttpException('You have already reviewed this product from that order.');
            }

            $model = new ProductReview([
                'user_id' => $userId,
                'product_id' => $productId,
                'order_id' => $orderId,
                'rating' => $rating,
                'review' => $review,
            ]);
            if (!$model->save()) {
                throw new BadRequestHttpException(implode(' ', $model->getFirstErrors()));
            }

            NotificationService::notifyManagers(
                NotificationService::TYPE_REVIEW_CREATED,
                "New {$rating}-star review for {$product->name}.",
                '/product/view',
                $productId,
            );
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        return $model;
    }
}
