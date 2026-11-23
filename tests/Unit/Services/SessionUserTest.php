<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\ApplicationSettings;
use app\models\User;
use app\services\SessionUser;
use Codeception\Test\Unit;
use Yii;
use yii\web\Cookie;
use yii\web\Request;

final class SessionUserTest extends Unit
{
    public function testExpiredSessionCannotImmediatelyRestoreRememberMeCookie(): void
    {
        $settings = ApplicationSettings::current();
        $settings->idle_minutes = 1;
        self::assertTrue($settings->save());
        $user = $this->createUser();
        $request = new SessionTestRequest(['cookieValidationKey' => 'test']);
        Yii::$app->set('request', $request);
        $this->resetUser();
        self::assertTrue(Yii::$app->user->login($user, 60));
        $request = new SessionTestRequest(['cookieValidationKey' => 'test']);
        $request->identityCookie = Yii::$app->response->cookies->getValue('_identity');
        Yii::$app->set('request', $request);
        self::assertSame($request->identityCookie, $request->cookies->getValue('_identity'));
        Yii::$app->session->set('__last_user_activity', time() - 61);
        $this->resetUser();

        self::assertTrue(Yii::$app->user->isGuest);
        self::assertNull(Yii::$app->session->get('__id'));
    }

    public function testOldLongLivedRememberMeCookieIsRejected(): void
    {
        $settings = ApplicationSettings::current();
        $settings->idle_minutes = 1;
        self::assertTrue($settings->save());
        $user = $this->createUser();
        $request = new SessionTestRequest(['cookieValidationKey' => 'test']);
        $request->identityCookie = json_encode([$user->id, $user->getAuthKey(), 86400]);
        Yii::$app->set('request', $request);
        Yii::$app->session->remove('__id');
        Yii::$app->session->remove('__last_user_activity');
        $this->resetUser();

        self::assertTrue(Yii::$app->user->isGuest);
    }

    public function testValidRememberMeCookieStillRestoresAnUnexpiredLogin(): void
    {
        $settings = ApplicationSettings::current();
        $settings->idle_minutes = 1;
        self::assertTrue($settings->save());
        $user = $this->createUser();
        $request = new SessionTestRequest(['cookieValidationKey' => 'test']);
        $request->identityCookie = json_encode([$user->id, $user->getAuthKey(), '60:' . (time() + 60)]);
        Yii::$app->set('request', $request);
        Yii::$app->session->remove('__id');
        Yii::$app->session->remove('__last_user_activity');
        $this->resetUser();

        self::assertFalse(Yii::$app->user->isGuest);
        self::assertEquals($user->id, Yii::$app->user->id);
    }

    public function testExpiredRememberMeCookieCannotBeReplayedWithoutASession(): void
    {
        $settings = ApplicationSettings::current();
        $settings->idle_minutes = 1;
        self::assertTrue($settings->save());
        $user = $this->createUser();
        $request = new SessionTestRequest(['cookieValidationKey' => 'test']);
        $request->identityCookie = json_encode([$user->id, $user->getAuthKey(), '60:' . (time() - 1)]);
        Yii::$app->set('request', $request);
        Yii::$app->session->remove('__id');
        Yii::$app->session->remove('__last_user_activity');
        $this->resetUser();

        self::assertTrue(Yii::$app->user->isGuest);
    }

    public function testOnlyUserActivityRenewsRememberMeCookie(): void
    {
        $settings = ApplicationSettings::current();
        $settings->idle_minutes = 1;
        self::assertTrue($settings->save());
        $user = $this->createUser();
        $request = new SessionTestRequest(['cookieValidationKey' => 'test']);
        Yii::$app->set('request', $request);
        $this->resetUser();
        self::assertTrue(Yii::$app->user->login($user, 60));
        $lastActivity = time() - 50;
        Yii::$app->session->set('__last_user_activity', $lastActivity);
        Yii::$app->response->cookies->removeAll();
        $request = new SessionTestRequest(['cookieValidationKey' => 'test']);
        $request->identityCookie = json_encode([$user->id, $user->getAuthKey(), '60:' . (time() + 10)]);
        Yii::$app->set('request', $request);
        $this->resetUser();

        self::assertFalse(Yii::$app->user->isGuest);
        self::assertNull(Yii::$app->response->cookies->getValue('_identity'));
        self::assertSame($lastActivity, Yii::$app->session->get('__last_user_activity'));
        $request->route = 'session/activity';
        $this->resetUser();
        self::assertFalse(Yii::$app->user->isGuest);
        $renewed = json_decode(Yii::$app->response->cookies->getValue('_identity'), true);
        self::assertGreaterThan(time() + 10, (int) explode(':', $renewed[2])[1]);
    }

    private function resetUser(): void
    {
        Yii::$app->set('user', [
            'class' => SessionUser::class,
            'identityClass' => User::class,
            'enableAutoLogin' => true,
        ]);
    }

    private function createUser(): User
    {
        $name = 'session_' . bin2hex(random_bytes(4));
        $user = new User([
            'username' => $name,
            'email' => $name . '@example.test',
            'full_name' => 'Session test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('SessionTest123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));
        return $user;
    }
}

final class SessionTestRequest extends Request
{
    public ?string $identityCookie = null;
    public string $route = 'session/status';

    protected function loadCookies()
    {
        return $this->identityCookie === null ? [] : [
            '_identity' => new Cookie(['name' => '_identity', 'value' => $this->identityCookie]),
        ];
    }

    public function resolve()
    {
        return [$this->route, []];
    }
}
