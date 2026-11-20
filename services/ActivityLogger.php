<?php

declare(strict_types=1);

namespace app\services;

use app\models\ActivityLog;
use Yii;
use yii\base\ActionEvent;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\Response;
use yii\web\User;
use yii\web\UserEvent;

final class ActivityLogger implements BootstrapInterface
{
    public function bootstrap($app): void
    {
        $route = null;
        $app->on(Application::EVENT_BEFORE_REQUEST, static function () use (&$route): void {
            $route = null;
        });
        $app->on(Module::EVENT_AFTER_ACTION, static function (ActionEvent $event) use (&$route): void {
            $route = $event->action->uniqueId;
        });
        $app->user->on(User::EVENT_AFTER_LOGIN, static function (UserEvent $event) use ($app): void {
            self::record(ActivityLog::LOGIN, $event->identity->getId(), $app->controller?->route);
        });
        $app->user->on(User::EVENT_AFTER_LOGOUT, static function (UserEvent $event) use ($app): void {
            self::record(ActivityLog::LOGOUT, $event->identity->getId(), $app->controller?->route);
        });
        $app->response->on(Response::EVENT_BEFORE_SEND, static function () use ($app, &$route): void {
            if (
                $app->user->isGuest || !$app->request->isGet || $app->request->isAjax
                || $app->response->statusCode !== 200 || $app->response->format !== Response::FORMAT_HTML
                || $route === null
            ) {
                return;
            }

            self::record(ActivityLog::VISIT, $app->user->id, $route);
        });
    }

    private static function record(string $event, int|string $userId, ?string $route = null): void
    {
        Yii::$app->db->createCommand()->insert(ActivityLog::tableName(), [
            'user_id' => $userId,
            'event' => $event,
            'route' => $route,
            'created_at' => time(),
        ])->execute();
    }
}
