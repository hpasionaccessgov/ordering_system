<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Product;
use app\models\ProductReview;
use app\services\ReviewService;
use Yii;
use yii\filters\AccessControl;

final class ReviewController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['index'], 'allow' => true, 'roles' => ['@']],
                ['actions' => ['create'], 'allow' => true, 'roles' => ['review.create']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
            'create' => ['POST'],
        ];
    }

    public function actionIndex(): array
    {
        $productId = filter_var(Yii::$app->request->getQueryParam('product_id'), FILTER_VALIDATE_INT);
        if ($productId === false || $productId < 1) {
            $this->fail(422, 'validation_failed', 'Please correct the errors below.', [
                'product_id' => ['product_id must be a positive integer.'],
            ]);
        }

        $productId = (int) $productId;
        $reviews = array_map(
            static fn(ProductReview $review): array => $review->toArray(['id', 'rating', 'review', 'created_at']),
            ReviewService::forProduct($productId),
        );

        return $this->ok([
            'product_id' => $productId,
            'reviews' => $reviews,
            'summary' => ReviewService::summaries([$productId])[$productId] ?? ['average' => 0.0, 'count' => 0],
        ]);
    }

    public function actionCreate(): array
    {
        $productId = $this->bodyInt('product_id', 1, PHP_INT_MAX);
        $orderId = $this->bodyInt('order_id', 1, PHP_INT_MAX);
        $rating = $this->bodyInt('rating', 1, 5);
        $review = Yii::$app->request->getBodyParams()['review'] ?? null;
        if (!is_string($review)) {
            $this->fail(422, 'validation_failed', 'Please correct the errors below.', [
                'review' => ['review must be a string.'],
            ]);
        }
        $review = trim($review);
        if ($review === '' || mb_strlen($review) > 1000) {
            $this->fail(422, 'validation_failed', 'Please correct the errors below.', [
                'review' => ['review must be between 1 and 1000 characters.'],
            ]);
        }
        if (!Product::find()->where(['id' => $productId, 'status' => Product::STATUS_ACTIVE])->exists()) {
            $this->fail(404, 'not_found', 'Product not found.');
        }

        $model = ReviewService::create((int) Yii::$app->user->id, $productId, $orderId, $rating, $review);

        return $this->ok($model->toArray(['id', 'product_id', 'order_id', 'rating', 'review', 'created_at']), [], 201);
    }
}
