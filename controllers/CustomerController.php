<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Order;
use app\models\Product;
use app\models\ApplicationSettings;
use app\models\Notification;
use app\services\FavoriteService;
use app\services\LoyaltyService;
use app\services\ProductReportService;
use app\services\ReviewService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;

class CustomerController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['customer.access'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';
        $userId = (int) Yii::$app->user->id;
        $featuredDataProvider = new ActiveDataProvider([
            'query' => Product::find()
                ->with(['categoryRecord', 'productIngredients.inventory'])
                ->where([
                    'status' => Product::STATUS_ACTIVE,
                    'is_featured' => true,
                ])
                ->orderBy(['name' => SORT_ASC]),
            'pagination' => ApplicationSettings::pagination(true),
            'sort' => false,
        ]);
        $featuredProducts = $featuredDataProvider->getModels();

        return $this->render('index', [
            'featuredDataProvider' => $featuredDataProvider,
            'orderCount' => (int) Order::find()
                ->where(['user_id' => $userId])
                ->count(),
            'totalSpent' => (float) Order::find()
                ->where(['user_id' => $userId, 'status' => Order::STATUS_COMPLETED])
                ->sum('total_amount'),
            'favoriteCount' => FavoriteService::countFor($userId),
            'favoriteProduct' => FavoriteService::latestProduct($userId),
            'favoriteIds' => FavoriteService::ids($userId),
            'recentOrders' => Order::find()
                ->where(['user_id' => $userId])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit(5)
                ->all(),
            'recentNotifications' => Notification::visibleTo($userId, false)
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
                ->limit(5)
                ->all(),
            'loyalty' => LoyaltyService::summary($userId),
            'ratingSummaries' => ReviewService::summaries(array_map(
                static fn(Product $product): int => (int) $product->id,
                $featuredProducts,
            )),
            'bestSellers' => (new ProductReportService())->bestSellers(5),
        ]);
    }
}
