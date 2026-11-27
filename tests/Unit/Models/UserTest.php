<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\User;
use app\modules\api\models\ApiToken;

final class UserTest extends \Codeception\Test\Unit
{
    private User $user;

    protected function _before(): void
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $this->user = new User([
            'username' => 'unit_user_' . $suffix,
            'email' => $suffix . '@unit.test',
            'full_name' => 'Unit user',
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->user->setPassword('UnitUser123!');
        $this->user->generateAuthKey();
        verify($this->user->save())->true();
    }

    public function testFindUserById()
    {
        /** @var User $user */
        $user = User::findIdentity($this->user->id);

        verify($user)->notEmpty();
        verify($user->username)->equals($this->user->username);
        verify(User::findIdentity(PHP_INT_MAX))->empty();
    }

    public function testFindUserByAccessToken()
    {
        $issued = ApiToken::issue((int) $this->user->id);
        /** @var User $user */
        $user = User::findIdentityByAccessToken($issued['token']);

        verify($user)->notEmpty();
        verify($user->username)->equals($this->user->username);
        verify(User::findIdentityByAccessToken('non-existing'))->empty();
    }

    public function testFindUserByUsername()
    {
        /** @var User $user */
        $user = User::findByUsername($this->user->username);

        verify($user)->notEmpty();
        verify(User::findByUsername('not-' . $this->user->username))->empty();
    }

    public function testValidateUser()
    {
        verify($this->user->validateAuthKey((string) $this->user->auth_key))->notEmpty();
        verify($this->user->validateAuthKey('wrong-auth-key'))->empty();
    }
}
