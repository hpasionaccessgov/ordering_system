<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\OrderNotification;
use app\services\OrderAccessPolicy;
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
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'unread-count'], 'roles' => ['notification.index']],
                    ['allow' => true, 'actions' => ['read'], 'roles' => ['notification.read']],
                ],
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
                'query' => $this->visibleNotifications()
                    ->with('order')
                    ->orderBy(['id' => SORT_DESC]),
                'pagination' => ['pageSize' => 9, 'pageSizeLimit' => [9, 9]],
                'sort' => false,
            ]),
        ]);
    }

    public function actionRead(int $id): Response
    {
        $notification = $this->visibleNotifications()->andWhere(['id' => $id])->one();
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

    public function actionUnreadCount(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->headers->set('Cache-Control', 'no-store');

        return ['count' => (int) $this->visibleNotifications()->andWhere(['read_at' => null])->count()];
    }

    private function visibleNotifications(): \yii\db\ActiveQuery
    {
        $canViewAll = Yii::$app->user->can('order.view') && OrderAccessPolicy::canViewAllOrders(
            Yii::$app->user->can('admin.access'),
            Yii::$app->user->can('staff.access'),
        );

        return OrderNotification::visibleTo(Yii::$app->user->id, $canViewAll);
    }
}
