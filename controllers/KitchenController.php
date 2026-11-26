<?php

declare(strict_types=1);

namespace app\controllers;

use app\services\OrderAccessPolicy;
use app\services\OrderBoardService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class KitchenController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['order.board', 'customer.access'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';
        $service = new OrderBoardService(
            (int) Yii::$app->user->id,
            OrderAccessPolicy::canViewAllOrders(
                Yii::$app->user->can('admin.access'),
                Yii::$app->user->can('staff.access'),
            ),
        );

        return $this->render('index', [
            'columns' => $service->boardColumns(),
            'counts' => $service->statusCounts(),
            'completedToday' => $service->completedToday(),
            'averagePreparationMinutes' => $service->averagePreparationMinutesToday(),
            'statuses' => $service->activeStatuses(),
        ]);
    }
}
