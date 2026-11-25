<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Inventory;
use app\models\Order;
use app\services\OrderBoardService;
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
        $board = new OrderBoardService();
        $statusCounts = OrderStatistics::statusCounts();

        return $this->render('dashboard', [
            'pendingOrderCount' => (int) ($statusCounts[Order::STATUS_PENDING] ?? 0),
            'processingOrderCount' => (int) ($statusCounts[Order::STATUS_PROCESSING] ?? 0),
            'readyForPickupCount' => (int) ($statusCounts[Order::STATUS_READY_FOR_PICKUP] ?? 0),
            'completedTodayCount' => $board->completedToday(),
            'averagePreparationMinutes' => $board->averagePreparationMinutesToday(),
            'lowStockCount' => (int) Inventory::find()->lowStock()->count(),
            'lowStockItems' => Inventory::find()->lowStock()->orderBy(['current_stock' => SORT_ASC])->all(),
            'orderStatusCounts' => $statusCounts,
        ]);
    }
}
