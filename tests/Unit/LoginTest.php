<?php

declare(strict_types=1);

namespace app\tests\Unit;

use app\controllers\SiteController;
use app\models\LoginForm;
use Yii;
use yii\base\Security;
use yii\web\View;

final class LoginTest extends \Codeception\Test\Unit
{
    public function testRenderLoginWrongUsername(): void
    {
        Yii::$app->request->setScriptUrl('/index.php');
        Yii::$app->user->logout();

        $model = new LoginForm(new Security(), [
            'username' => 'missing_' . substr(bin2hex(random_bytes(4)), 0, 8),
            'password' => 'wrong_password',
        ]);
        self::assertFalse($model->login());

        $controller = new SiteController(
            'site',
            Yii::$app,
            Yii::$app->mailer,
            new Security(),
        );
        $view = new View(['context' => $controller]);
        Yii::$app->controller = $controller;

        $html = $view->render('//layouts/main.php', ['content' => 'Hello World°']);

        self::assertStringContainsString('Sign in', $html);
        self::assertStringNotContainsString('Sign out', $html);
    }
}
