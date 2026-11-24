<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Inventory;
use app\models\Order;
use app\models\Product;
use app\services\OrderStatistics;
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

    public function actionDashboard(): string
    {
        $this->layout = 'admin';

        return $this->render('dashboard', [
            'activeProductCount' => (int) Product::find()->where(['status' => Product::STATUS_ACTIVE])->count(),
            'pendingOrderCount' => (int) Order::find()->where(['status' => Order::STATUS_PENDING])->count(),
            'processingOrderCount' => (int) Order::find()->where([
                'status' => Order::STATUS_PROCESSING,
            ])->count(),
            'inventoryCount' => (int) Inventory::find()->active()->count(),
            'lowStockCount' => (int) Inventory::find()->lowStock()->count(),
            'outOfStockCount' => (int) Inventory::find()->outOfStock()->count(),
            'lowStockItems' => Inventory::find()->lowStock()->orderBy(['current_stock' => SORT_ASC])->all(),
            'orderStatusCounts' => OrderStatistics::statusCounts(),
        ]);
    }
}
