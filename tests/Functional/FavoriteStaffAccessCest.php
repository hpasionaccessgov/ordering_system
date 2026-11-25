<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\User;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class FavoriteStaffAccessCest
{
    public function staffCannotOpenFavorites(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $staff = new User([
            'username' => 'favorite_staff_' . $suffix,
            'email' => $suffix . '@favorite-staff.test',
            'full_name' => 'Favorite staff',
            'status' => User::STATUS_ACTIVE,
        ]);
        $staff->setPassword('FavoriteStaff123!');
        $staff->generateAuthKey();
        $I->assertTrue($staff->save(), json_encode($staff->getErrors()));
        Yii::$app->authManager->assign(
            Yii::$app->authManager->getRole('staff'),
            $staff->id,
        );

        $I->amLoggedInAs($staff);
        $I->amOnRoute('favorite/index');
        $I->seeResponseCodeIs(403);
    }
}
