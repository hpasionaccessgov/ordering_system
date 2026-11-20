<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Order;
use app\models\Product;
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

    public function actionDashboard(): string
    {
        $this->layout = 'admin';

        return $this->render('dashboard', [
            'featuredDataProvider' => new ActiveDataProvider([
                'query' => Product::find()
                    ->where([
                        'status' => Product::STATUS_ACTIVE,
                        'is_featured' => true,
                    ])
                    ->orderBy(['name' => SORT_ASC]),
                'pagination' => ['pageSize' => 6, 'pageSizeLimit' => [6, 6]],
                'sort' => false,
            ]),
            'orderCount' => (int) Order::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->count(),
        ]);
    }
}
