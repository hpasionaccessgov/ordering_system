<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\OrderNotification;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class NotificationController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['read' => ['post']],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => OrderNotification::find()
                    ->where(['user_id' => Yii::$app->user->id])
                    ->with('order')
                    ->orderBy(['id' => SORT_DESC]),
                'pagination' => ['pageSize' => 20],
                'sort' => false,
            ]),
        ]);
    }

    public function actionRead(int $id): Response
    {
        $notification = OrderNotification::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        if ($notification === null) {
            throw new NotFoundHttpException('Notification not found.');
        }
        if ($notification->read_at === null) {
            $notification->read_at = time();
            if (!$notification->save(false, ['read_at'])) {
                throw new \RuntimeException('Notification could not be marked as read.');
            }
        }

        return $this->redirect(['index']);
    }
}
