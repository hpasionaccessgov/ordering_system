<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Order;
use app\services\FavoriteService;
use Yii;
use yii\filters\AccessControl;

final class CustomerController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['summary'], 'allow' => true, 'roles' => ['customer.access']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'summary' => ['GET'],
        ];
    }

    public function actionSummary(): array
    {
        $userId = (int) Yii::$app->user->id;
        $favorite = FavoriteService::latestProduct($userId);

        return $this->ok([
            'order_count' => (int) Order::find()->where(['user_id' => $userId])->count(),
            'total_spent' => (string) (Order::find()
                ->where(['user_id' => $userId, 'status' => Order::STATUS_COMPLETED])
                ->sum('total_amount') ?? '0'),
            'favorite_count' => FavoriteService::countFor($userId),
            'favorite_product' => $favorite === null ? null : $favorite->toArray(['id', 'name', 'price']),
        ]);
    }
}
