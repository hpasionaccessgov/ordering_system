<?php

declare(strict_types=1);

namespace app\controllers;

use app\services\OrderBoardService;
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
                        'roles' => ['order.board'],
                    ],
                ],
            ],
        ];
    }

    public function actionBoard(): string
    {
        $this->layout = 'admin';
        $service = new OrderBoardService();

        return $this->render('board', [
            'columns' => $service->boardColumns(),
            'counts' => $service->statusCounts(),
            'completedToday' => $service->completedToday(),
            'averagePreparationMinutes' => $service->averagePreparationMinutesToday(),
            'statuses' => $service->activeStatuses(),
        ]);
    }
}
