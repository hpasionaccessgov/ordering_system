<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Inventory;
use app\models\Order;
use app\models\Product;
use app\services\FavoriteService;
use app\services\OrderBoardService;
use app\services\OrderStatistics;
use app\services\ProductReportService;
use app\services\ReviewService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class StaffController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['staff.access'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';
        $board = new OrderBoardService();
        $statusCounts = OrderStatistics::statusCounts();
        $userId = (int) Yii::$app->user->id;
        $featuredProducts = Product::find()
            ->with(['categoryRecord', 'productIngredients.inventory'])
            ->where(['status' => Product::STATUS_ACTIVE, 'is_featured' => true])
            ->orderBy(['name' => SORT_ASC])
            ->limit(6)
            ->all();

        return $this->render('index', [
            'pendingOrderCount' => (int) ($statusCounts[Order::STATUS_PENDING] ?? 0),
            'processingOrderCount' => (int) ($statusCounts[Order::STATUS_PROCESSING] ?? 0),
            'readyForPickupCount' => (int) ($statusCounts[Order::STATUS_READY_FOR_PICKUP] ?? 0),
            'completedTodayCount' => $board->completedToday(),
            'averagePreparationMinutes' => $board->averagePreparationMinutesToday(),
            'lowStockCount' => (int) Inventory::find()->lowStock()->count(),
            'lowStockItems' => Inventory::find()->lowStock()->orderBy(['current_stock' => SORT_ASC])->all(),
            'orderStatusCounts' => $statusCounts,
            'featuredProducts' => $featuredProducts,
            'favoriteIds' => FavoriteService::ids($userId),
            'ratingSummaries' => ReviewService::summaries(array_map(
                static fn(Product $product): int => (int) $product->id,
                $featuredProducts,
            )),
            'bestSellers' => (new ProductReportService())->bestSellers(5),
        ]);
    }
}
