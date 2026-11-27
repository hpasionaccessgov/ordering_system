<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\LoginForm;
use app\models\User;
use app\modules\api\models\ApiToken;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

final class AuthController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['login'];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['login'], 'allow' => true, 'roles' => ['?', '@']],
                ['actions' => ['logout', 'profile'], 'allow' => true, 'roles' => ['@']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'login' => ['POST'],
            'logout' => ['POST'],
            'profile' => ['GET'],
        ];
    }

    public function actionLogin(): array
    {
        $form = new LoginForm(Yii::$app->security);
        $form->load(Yii::$app->request->getBodyParams(), '');
        if (!$form->validate()) {
            $message = (string) ($form->getFirstErrors()[array_key_first($form->getFirstErrors())] ?? 'Invalid credentials.');
            $this->fail(401, 'invalid_credentials', $message, ['credentials' => array_values($form->getFirstErrors())]);
        }

        $user = $form->getUser();
        $issued = ApiToken::issue((int) $user->id);

        return $this->ok([
            'token' => $issued['token'],
            'token_type' => 'Bearer',
            'expires_in' => ApiToken::TTL_SECONDS,
            'expires_at' => $issued['expires_at'],
        ]);
    }

    public function actionLogout(): array
    {
        $header = (string) Yii::$app->request->getHeaders()->get('Authorization', '');
        if (preg_match('/^Bearer\s+(.+)$/', $header, $matches) === 1) {
            ApiToken::revoke($matches[1]);
        }

        return $this->ok(['revoked' => true]);
    }

    public function actionProfile(): array
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;

        return $this->ok([
            'id' => (int) $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => $user->full_name,
            'status' => (int) $user->status,
        ]);
    }
}
