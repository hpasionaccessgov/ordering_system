<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\LoginForm;
use app\models\ApplicationSettings;
use app\models\User;
use Codeception\Test\Unit;
use Yii;
use yii\caching\ArrayCache;

final class LoginThrottleTest extends Unit
{
    protected function _before(): void
    {
        parent::_before();
        Yii::$app->set('cache', TrackingLoginCache::class);
    }

    public function testInactiveAccountUsesSpecificMessageAfterCorrectPassword(): void
    {
        $user = $this->createUser('disabled-login', User::STATUS_DISABLED);
        $form = Yii::createObject(LoginForm::class);
        $form->username = $user->username;
        $form->password = 'LoginThrottle123!';

        self::assertFalse($form->login());
        self::assertSame(
            'This account has been disabled. Contact an administrator.',
            $form->getFirstError('password'),
        );
    }

    public function testRepeatedFailuresBlockLoginTemporarily(): void
    {
        $user = $this->createUser('throttled-login', User::STATUS_ACTIVE);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $form = Yii::createObject(LoginForm::class);
            $form->username = $user->username;
            $form->password = 'wrong-password';
            self::assertFalse($form->login());
        }

        $blocked = Yii::createObject(LoginForm::class);
        $blocked->username = $user->username;
        $blocked->password = 'LoginThrottle123!';
        self::assertFalse($blocked->login());
        self::assertStringContainsString(
            'Too many login attempts.',
            (string) $blocked->getFirstError('password'),
        );
    }

    public function testConfiguredAttemptWindowAndLockDuration(): void
    {
        $settings = ApplicationSettings::current();
        $settings->login_max_attempts = 2;
        $settings->login_reset_minutes = 3;
        $settings->login_lock_minutes = 2;
        self::assertTrue($settings->save());
        $user = $this->createUser('configured-login', User::STATUS_ACTIVE);
        for ($i = 0; $i < 2; $i++) {
            $form = Yii::createObject(LoginForm::class);
            $form->username = $user->username;
            $form->password = 'wrong-password';
            self::assertFalse($form->login());
        }
        self::assertSame([180, 120], Yii::$app->cache->durations);
        $form = Yii::createObject(LoginForm::class);
        $form->username = $user->username;
        $form->password = 'LoginThrottle123!';
        self::assertFalse($form->login());
        self::assertStringContainsString('Too many login attempts.', (string) $form->getFirstError('password'));
    }

    private function createUser(string $username, int $status): User
    {
        $user = new User([
            'username' => $username . '_' . substr(bin2hex(random_bytes(3)), 0, 6),
            'email' => substr(bin2hex(random_bytes(4)), 0, 8) . '@login.test',
            'full_name' => 'Login test',
            'status' => $status,
        ]);
        $user->setPassword('LoginThrottle123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));

        return $user;
    }

}

final class TrackingLoginCache extends ArrayCache
{
    public array $durations = [];

    protected function setValue($key, $value, $duration)
    {
        $this->durations[] = $duration;
        return parent::setValue($key, $value, $duration);
    }
}
