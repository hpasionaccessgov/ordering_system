<?php

declare(strict_types=1);

namespace app\controllers;

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

        return $this->render('dashboard');
    }
}
