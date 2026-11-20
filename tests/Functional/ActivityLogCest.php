<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\ActivityLog;
use app\models\User;
use app\services\ActivityLogger;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\caching\ArrayCache;
use yii\rbac\DbManager;

final class ActivityLogCest
{
    public function _before(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        Yii::$app->set('cache', ArrayCache::class);
        (new ActivityLogger())->bootstrap(Yii::$app);
    }

    public function recordsSuccessfulAuthenticationAndSignedInVisits(FunctionalTester $I): void
    {
        $user = $this->user($I, 'customer');
        $existing = (int) ActivityLog::find()->count();
        $I->amOnRoute('site/login');
        $I->assertSame($existing, (int) ActivityLog::find()->count());
        $I->submitForm('#login-form', [
            'LoginForm[username]' => $user->username,
            'LoginForm[password]' => 'IncorrectPassword!',
        ]);
        $I->assertSame(0, (int) ActivityLog::find()->where(['user_id' => $user->getPrimaryKey()])->count());
        $I->submitForm('#login-form', [
            'LoginForm[username]' => $user->username,
            'LoginForm[password]' => 'ActivityTest123!',
        ]);
        $I->assertSame(1, (int) ActivityLog::find()->where([
            'user_id' => $user->getPrimaryKey(),
            'event' => ActivityLog::LOGIN,
        ])->count());
        $I->assertSame('site/login', ActivityLog::findOne([
            'user_id' => $user->getPrimaryKey(),
            'event' => ActivityLog::LOGIN,
        ])->route);

        $I->amOnRoute('site/profile', ['secret' => 'not-stored']);
        $I->seeResponseCodeIs(200);
        $I->assertSame(1, (int) ActivityLog::find()->where([
            'user_id' => $user->getPrimaryKey(),
            'event' => ActivityLog::VISIT,
            'route' => 'site/profile',
        ])->count());
        $I->assertSame(0, (int) ActivityLog::find()->where(['like', 'route', 'not-stored'])->count());
        $before = (int) ActivityLog::find()->where(['user_id' => $user->getPrimaryKey(), 'event' => ActivityLog::VISIT])->count();
        $I->sendAjaxGetRequest('/index.php?r=notification%2Funread-count');
        $I->assertSame($before, (int) ActivityLog::find()->where([
            'user_id' => $user->getPrimaryKey(),
            'event' => ActivityLog::VISIT,
        ])->count());
        $I->sendAjaxPostRequest('/index.php?r=site%2Flogout');
        $I->assertSame(1, (int) ActivityLog::find()->where([
            'user_id' => $user->getPrimaryKey(),
            'event' => ActivityLog::LOGOUT,
        ])->count());
        $I->assertSame('site/logout', ActivityLog::findOne([
            'user_id' => $user->getPrimaryKey(),
            'event' => ActivityLog::LOGOUT,
        ])->route);
    }

    public function activityPageIsAdminOnlyAndSidebarGroupsStartOpen(FunctionalTester $I): void
    {
        $admin = $this->user($I, 'admin');
        $staff = $this->user($I, 'staff');
        $I->amLoggedInAs($staff);
        $I->amOnRoute('admin/activity');
        $I->seeResponseCodeIs(403);
        $I->amOnRoute('staff/dashboard');
        $I->dontSee('Activity logs');
        $I->amLoggedInAs($admin);
        $I->amOnRoute('admin/activity');
        $I->seeResponseCodeIs(200);
        $I->see('Activity logs', 'h1');
        $I->see($admin->username);
        $I->seeElement('.sidebar-menu > .nav-item.menu-open');
        $I->see('Categories');
        $I->see('Roles');
        $I->submitForm('form[role="search"]', ['event' => 'logout']);
        $I->dontSee('Login', 'td');
    }

    public function deletingAUserKeepsTheirActivityWithoutIdentity(FunctionalTester $I): void
    {
        $user = $this->user($I, 'customer');
        $I->amLoggedInAs($user);
        $log = ActivityLog::findOne(['user_id' => $user->getPrimaryKey(), 'event' => ActivityLog::LOGIN]);
        $I->assertNotNull($log);
        Yii::$app->user->logout();
        $I->assertSame(1, $user->delete());
        $log->refresh();
        $I->assertNull($log->user_id);
    }

    private function user(FunctionalTester $I, string $role): User
    {
        $name = $role . '_' . bin2hex(random_bytes(4));
        $user = new User([
            'username' => $name,
            'email' => $name . '@example.test',
            'full_name' => 'Activity test user',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('ActivityTest123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole($role), $user->id);

        return $user;
    }
}
