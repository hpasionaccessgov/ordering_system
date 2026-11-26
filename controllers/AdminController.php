<?php

declare(strict_types=1);

namespace app\controllers;

use app\services\Navigation;

use app\models\Inventory;
use app\models\Order;
use app\models\Product;
use app\models\User;
use app\services\FavoriteService;
use app\services\OrderStatistics;
use app\services\ProductReportService;
use app\services\ReviewService;
use app\services\SalesReportService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class AdminController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin.access'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';
        $userId = (int) Yii::$app->user->id;
        $featuredProducts = Product::find()
            ->with(['categoryRecord', 'productIngredients.inventory'])
            ->where(['status' => Product::STATUS_ACTIVE, 'is_featured' => true])
            ->orderBy(['name' => SORT_ASC])
            ->limit(6)
            ->all();

        return $this->render('index', [
            'userCount' => (int) User::find()->count(),
            'activeUserCount' => (int) User::find()->where(['status' => User::STATUS_ACTIVE])->count(),
            'productCount' => (int) Product::find()->where(['status' => Product::STATUS_ACTIVE])->count(),
            'pendingOrderCount' => (int) Order::find()->where(['status' => Order::STATUS_PENDING])->count(),
            'inventoryCount' => (int) Inventory::find()->active()->count(),
            'lowStockCount' => (int) Inventory::find()->lowStock()->count(),
            'outOfStockCount' => (int) Inventory::find()->outOfStock()->count(),
            'lowStockItems' => Inventory::find()->lowStock()->orderBy(['current_stock' => SORT_ASC])->all(),
            'orderStatusCounts' => OrderStatistics::statusCounts(),
            'todaySales' => (new SalesReportService())->todaySummary(),
            'featuredProducts' => $featuredProducts,
            'favoriteIds' => FavoriteService::ids($userId),
            'ratingSummaries' => ReviewService::summaries(array_map(
                static fn(Product $product): int => (int) $product->id,
                $featuredProducts,
            )),
            'bestSellers' => (new ProductReportService())->bestSellers(5),
        ]);
    }

    public function actionActivity(): Response
    {
        $params = $this->request->queryParams;
        unset($params['r']);

        return $this->redirect(array_merge(Navigation::url(Navigation::ACTIVITY_LOG_INDEX), $params));
    }
}
