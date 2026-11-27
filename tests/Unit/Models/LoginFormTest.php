<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\base\Security;

final class LoginFormTest extends \Codeception\Test\Unit
{
    private const PASSWORD = 'LoginForm123!';

    private $_model;
    private User $user;

    protected function _before(): void
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $this->user = new User([
            'username' => 'auth_form_' . $suffix,
            'email' => $suffix . '@auth-form.test',
            'full_name' => 'Login form test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->user->setPassword(self::PASSWORD);
        $this->user->generateAuthKey();
        verify($this->user->save())->true();
    }

    protected function _after()
    {
        Yii::$app->user->logout();
    }

    public function testLoginNoUser()
    {
        $this->_model = new LoginForm(
            new Security(),
            [
                'username' => 'not_existing_username',
                'password' => 'not_existing_password',
            ],
        );

        verify($this->_model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
    }

    public function testLoginWrongPassword()
    {
        $this->_model = new LoginForm(
            new Security(),
            [
                'username' => $this->user->username,
                'password' => 'wrong_password',
            ],
        );

        verify($this->_model->login())->false();
        verify(Yii::$app->user->isGuest)->true();
        verify($this->_model->errors)->arrayHasKey('password');
    }

    public function testLoginCorrect()
    {
        $this->_model = new LoginForm(
            new Security(),
            [
                'username' => $this->user->username,
                'password' => self::PASSWORD,
            ],
        );

        verify($this->_model->login())->true();
        verify(Yii::$app->user->isGuest)->false();
        verify($this->_model->errors)->arrayHasNotKey('password');
    }
}
