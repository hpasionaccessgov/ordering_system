<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Order;
use app\models\Product;
use app\models\User;
use app\services\OrderStatistics;
use yii\filters\AccessControl;
use yii\web\Controller;

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

    public function actionDashboard(): string
    {
        $this->layout = 'admin';

        return $this->render('dashboard', [
            'userCount' => (int) User::find()->count(),
            'activeUserCount' => (int) User::find()->where(['status' => User::STATUS_ACTIVE])->count(),
            'productCount' => (int) Product::find()->where(['status' => Product::STATUS_ACTIVE])->count(),
            'pendingOrderCount' => (int) Order::find()->where(['status' => Order::STATUS_PENDING])->count(),
            'orderStatusCounts' => OrderStatistics::statusCounts(),
        ]);
    }
}
