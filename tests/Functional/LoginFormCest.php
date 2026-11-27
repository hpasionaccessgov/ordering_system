<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\User;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class LoginFormCest
{
    public function _before(FunctionalTester $I)
    {
        Yii::$app->set('authManager', DbManager::class);
        $I->amOnRoute('site/login');
    }

    public function openLoginPage(FunctionalTester $I)
    {
        $I->see('Sign in to Cafébara', 'h1');
    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginById(FunctionalTester $I)
    {
        $user = $this->admin($I);
        $I->amLoggedInAs($user->id);
        $I->amOnPage('/');
        $I->see($user->username);
        $I->see('Sign out');
    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginByInstance(FunctionalTester $I)
    {
        $user = $this->admin($I);
        $I->amLoggedInAs($user);
        $I->amOnPage('/');
        $I->see($user->username);
        $I->see('Sign out');
    }

    public function loginWithEmptyCredentials(FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function loginWithWrongCredentials(FunctionalTester $I)
    {
        $user = $this->admin($I);
        $I->submitForm('#login-form', [
            'LoginForm[username]' => $user->username,
            'LoginForm[password]' => 'wrong',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password.');
    }

    public function loginSuccessfully(FunctionalTester $I)
    {
        $user = $this->admin($I);
        $I->submitForm('#login-form', [
            'LoginForm[username]' => $user->username,
            'LoginForm[password]' => 'LoginTest123!',
        ]);
        $I->see($user->username);
        $I->see('Sign out');
        $I->dontSeeElement('form#login-form');
    }

    private function admin(FunctionalTester $I): User
    {
        $user = new User([
            'username' => 'login_admin_' . substr(bin2hex(random_bytes(4)), 0, 8),
            'email' => bin2hex(random_bytes(8)) . '@login.test',
            'full_name' => 'Login fixture admin',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('LoginTest123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('admin'), $user->id);

        return $user;
    }
}
