<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\Notification;
use app\models\NotificationSearch;
use app\services\OrderAccessPolicy;
use Yii;
use yii\filters\AccessControl;

final class NotificationController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['index', 'unread-count'], 'allow' => true, 'roles' => ['notification.index']],
                ['actions' => ['read'], 'allow' => true, 'roles' => ['notification.read']],
                ['actions' => ['delete'], 'allow' => true, 'roles' => ['notification.delete']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
            'unread-count' => ['GET'],
            'read' => ['POST'],
            'delete' => ['DELETE'],
        ];
    }

    public function actionIndex(): array
    {
        $provider = (new NotificationSearch())->search(
            Yii::$app->request->getQueryParams(),
            Yii::$app->user->id,
            $this->canViewAll(),
        );

        $notifications = array_map(
            fn(Notification $notification): array => $notification->toArray([
                'id',
                'type',
                'status',
                'message',
                'target_route',
                'target_id',
                'category',
                'created_at',
                'read_at',
                'label',
                'categoryLabel',
                'targetUrl',
            ]),
            $provider->getModels(),
        );

        return $this->ok($notifications, ['pagination' => $this->paginationMeta($provider->getPagination())]);
    }

    public function actionUnreadCount(): array
    {
        return $this->ok(['count' => (int) $this->visibleNotifications()->andWhere(['read_at' => null])->count()]);
    }

    public function actionRead(int $id): array
    {
        $notification = $this->findNotification($id);
        if ($notification->read_at === null) {
            $notification->read_at = time();
            if (!$notification->save(false, ['read_at'])) {
                $this->fail(500, 'internal_error', 'Notification could not be marked as read.');
            }
        }

        return $this->ok($this->serializeNotification($notification));
    }

    public function actionDelete(int $id): array
    {
        $notification = $this->findNotification($id);
        if ($notification->delete() === false) {
            $this->fail(500, 'internal_error', 'Notification could not be deleted.');
        }

        return $this->ok(['deleted' => true]);
    }

    private function visibleNotifications(): \yii\db\ActiveQuery
    {
        return Notification::visibleTo(Yii::$app->user->id, $this->canViewAll());
    }

    private function canViewAll(): bool
    {
        return (bool) Yii::$app->user->can('order.view') && OrderAccessPolicy::canViewAllOrders(
            (bool) Yii::$app->user->can('admin.access'),
            (bool) Yii::$app->user->can('staff.access'),
        );
    }

    private function findNotification(int $id): Notification
    {
        $notification = $this->visibleNotifications()->andWhere(['id' => $id])->one();
        if ($notification === null) {
            $this->fail(404, 'not_found', 'Notification not found.');
        }

        return $notification;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeNotification(Notification $notification): array
    {
        return $notification->toArray([
            'id',
            'type',
            'status',
            'message',
            'target_route',
            'target_id',
            'category',
            'created_at',
            'read_at',
            'label',
            'categoryLabel',
            'targetUrl',
        ]);
    }
}
