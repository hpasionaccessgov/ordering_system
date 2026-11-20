<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\User;
use app\tests\Support\FunctionalTester;

final class ProfileCest
{
    public function profileChangesAreSavedAndDuplicatesRejected(FunctionalTester $I): void
    {
        $suffix = bin2hex(random_bytes(4));
        $users = [];
        foreach (['first', 'second'] as $name) {
            $user = new User([
                'username' => $name . '_' . $suffix,
                'email' => $name . '_' . $suffix . '@example.test',
                'full_name' => 'Profile Customer',
                'status' => User::STATUS_ACTIVE,
            ]);
            $user->setPassword('ProfileTest123!');
            $user->generateAuthKey();
            $I->assertTrue($user->save(), json_encode($user->getErrors()));
            $users[] = $user;
        }
        $I->amLoggedInAs($users[0]);
        $I->amOnRoute('site/profile');
        $I->seeInField('ProfileForm[username]', $users[0]->username);
        $I->seeInField('ProfileForm[email]', $users[0]->email);
        $I->submitForm('#profile-form', ['ProfileForm' => [
            'fullName' => 'Updated Customer',
            'username' => 'new_' . $suffix,
            'email' => 'new_' . $suffix . '@example.test',
        ]]);
        $users[0]->refresh();
        $I->assertSame('new_' . $suffix, $users[0]->username);
        $I->assertSame('new_' . $suffix . '@example.test', $users[0]->email);

        $I->submitForm('#profile-form', ['ProfileForm' => [
            'fullName' => 'Updated Customer',
            'username' => $users[1]->username,
            'email' => $users[1]->email,
        ]]);
        $I->see('has already been taken');
        $users[0]->refresh();
        $I->assertSame('new_' . $suffix, $users[0]->username);
        $I->assertSame('new_' . $suffix . '@example.test', $users[0]->email);
    }
}
