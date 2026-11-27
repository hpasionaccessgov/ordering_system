<?php

declare(strict_types=1);

namespace app\tests\Unit;

use app\controllers\SiteController;
use app\models\User;
use Yii;
use yii\base\Security;
use yii\web\View;

final class LogoutTest extends \Codeception\Test\Unit
{
    protected function _after(): void
    {
        Yii::$app->user->logout();
    }

    public function testRenderLogoutLinkWhenUserIsLoggedIn(): void
    {
        Yii::$app->request->setScriptUrl('/index.php');
        $user = $this->user();

        $controller = new SiteController(
            'site',
            Yii::$app,
            Yii::$app->mailer,
            new Security(),
        );
        $view = new View(['context' => $controller]);
        Yii::$app->controller = $controller;

        Yii::$app->user->login($user);

        $html = $view->render('//layouts/main.php', ['content' => 'Hello World°']);

        self::assertStringContainsString($user->username, $html);
        self::assertStringContainsString('Sign out', $html);
        self::assertStringContainsString('method="POST"', $html);

        $controller->actionLogout();

        $html = $view->render('//layouts/main.php', ['content' => 'Hello World°']);

        self::assertStringNotContainsString('Sign out', $html);
        self::assertStringContainsString('Sign in', $html);
    }

    private function user(): User
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = new User([
            'username' => 'account_' . $suffix,
            'email' => $suffix . '@account.test',
            'full_name' => 'Logout test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('Logout123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));

        return $user;
    }
}
