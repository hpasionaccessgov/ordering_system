<?php

declare(strict_types=1);

namespace app\tests\Unit\Controllers;

use app\controllers\SiteController;
use app\models\ProfileForm;
use app\models\User;
use Codeception\Test\Unit;
use Yii;

final class ProfileUpdateTest extends Unit
{
    public function testProfileUpdatesUsernameEmailAndFullName(): void
    {
        $user = $this->createUser();
        Yii::$app->user->setIdentity($user);
        $suffix = bin2hex(random_bytes(4));
        Yii::$app->request->setBodyParams(['ProfileForm' => [
            'fullName' => 'Updated Name',
            'username' => 'updated_' . $suffix,
            'email' => 'UPDATED_' . $suffix . '@example.test',
        ]]);
        $controller = Yii::createObject(SiteController::class, ['site', Yii::$app]);
        Yii::$app->controller = $controller;
        Yii::$app->request->setScriptUrl('/index.php');
        $controller->actionProfile();

        $user->refresh();
        self::assertSame('Updated Name', $user->full_name);
        self::assertSame('updated_' . $suffix, $user->username);
        self::assertSame('updated_' . $suffix . '@example.test', $user->email);
    }

    public function testProfileValidatesUniquenessAndEmail(): void
    {
        $user = $this->createUser();
        $other = $this->createUser();
        Yii::$app->user->setIdentity($user);
        $form = new ProfileForm([
            'fullName' => $user->full_name,
            'username' => $user->username,
            'email' => $user->email,
        ]);
        self::assertTrue($form->validate(), json_encode($form->getErrors()));
        $form->username = $other->username;
        $form->email = $other->email;
        self::assertFalse($form->validate());
        self::assertArrayHasKey('username', $form->getErrors());
        self::assertArrayHasKey('email', $form->getErrors());
        $form->username = $user->username;
        $form->email = 'invalid-email';
        self::assertFalse($form->validate());
        self::assertArrayHasKey('email', $form->getErrors());
    }

    private function createUser(): User
    {
        $name = 'profile_' . bin2hex(random_bytes(4));
        $user = new User([
            'username' => $name,
            'email' => $name . '@example.test',
            'full_name' => 'Profile User',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('ProfileTest123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));

        return $user;
    }
}
