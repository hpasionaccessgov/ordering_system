<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\ActivityLog;
use app\models\Order;
use app\models\Product;
use app\models\User;
use app\services\OrderStatistics;
use yii\data\ActiveDataProvider;
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

    public function actionActivity(): string
    {
        $this->layout = 'admin';
        $event = $this->request->get('event', '');
        $events = [ActivityLog::VISIT, ActivityLog::LOGIN, ActivityLog::LOGOUT];
        $event = is_string($event) && in_array($event, $events, true) ? $event : '';
        $query = ActivityLog::find()->with('user')->orderBy(['id' => SORT_DESC]);
        if ($event !== '') {
            $query->andWhere(['event' => $event]);
        }

        return $this->render('activity', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 25],
                'sort' => false,
            ]),
            'event' => $event,
        ]);
    }
}
