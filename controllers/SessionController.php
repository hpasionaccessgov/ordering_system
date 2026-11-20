<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class SessionController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['status' => ['get'], 'activity' => ['post']]],
        ];
    }

    public function actionStatus(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Cache-Control', 'no-store');
        return ['authenticated' => !Yii::$app->user->isGuest];
    }

    public function actionActivity(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Cache-Control', 'no-store');
        return ['authenticated' => !Yii::$app->user->isGuest];
    }
}
