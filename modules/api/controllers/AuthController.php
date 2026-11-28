<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\User;
use app\modules\api\models\ApiToken;
use app\services\RegistrationService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

final class AuthController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['login', 'register'];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['login', 'register'], 'allow' => true, 'roles' => ['?', '@']],
                ['actions' => ['logout', 'profile'], 'allow' => true, 'roles' => ['@']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'login' => ['POST'],
            'register' => ['POST'],
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

        return $this->ok($this->tokenPayload($form->getUser()));
    }

    public function actionRegister(): array
    {
        $form = new RegisterForm();
        $form->load(Yii::$app->request->getBodyParams(), '');
        if (!$form->validate()) {
            $this->fail(422, 'validation_failed', 'Please correct the errors below.', $form->getErrors());
        }

        $user = (new RegistrationService())->register($form);
        if ($user->hasErrors()) {
            $this->fail(422, 'registration_failed', 'We could not create your account.', $user->getErrors());
        }

        return $this->ok($this->tokenPayload($user));
    }

    /**
     * @return array{token: string, token_type: string, expires_in: int, expires_at: int}
     */
    private function tokenPayload(User $user): array
    {
        $issued = ApiToken::issue((int) $user->id);

        return [
            'token' => $issued['token'],
            'token_type' => 'Bearer',
            'expires_in' => ApiToken::TTL_SECONDS,
            'expires_at' => $issued['expires_at'],
        ];
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
