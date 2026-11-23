<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Notification;
use app\models\NotificationSearch;
use app\services\OrderAccessPolicy;
use Yii;
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
                    ['allow' => true, 'actions' => ['view', 'read'], 'roles' => ['notification.read']],
                    ['allow' => true, 'actions' => ['delete'], 'roles' => ['notification.delete']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['read' => ['post'], 'delete' => ['post']],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';

        $searchModel = new NotificationSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams,
            Yii::$app->user->id,
            $this->canViewAllNotifications(),
        );

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $searchModel->q,
            'status' => $searchModel->status,
            'read' => $searchModel->readFilter,
        ]);
    }

    public function actionView(int $id): Response
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

        return $this->redirect($notification->targetUrl);
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

    public function actionDelete(int $id): Response
    {
        $notification = $this->visibleNotifications()->andWhere(['id' => $id])->one();
        if ($notification === null) {
            throw new NotFoundHttpException('Notification not found.');
        }
        if ($notification->delete() === false) {
            throw new \RuntimeException('Notification could not be deleted.');
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
        return Notification::visibleTo(Yii::$app->user->id, $this->canViewAllNotifications());
    }

    private function canViewAllNotifications(): bool
    {
        return Yii::$app->user->can('order.view') && OrderAccessPolicy::canViewAllOrders(
            Yii::$app->user->can('admin.access'),
            Yii::$app->user->can('staff.access'),
        );
    }
}
