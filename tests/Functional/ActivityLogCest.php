<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\ActivityLog;
use app\models\ActivityLogSearch;
use app\models\ApplicationSettings;
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
        $I->amOnRoute('admin/activity', ['event' => 'logout']);
        $I->seeElement('select[name="event"] option[value="logout"][selected]');
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

    public function expiredLogsAreDeletedDuringRequests(FunctionalTester $I): void
    {
        $settings = ApplicationSettings::current();
        $settings->logs_expiration_days = 90;
        $I->assertTrue($settings->save());
        $suffix = bin2hex(random_bytes(4));
        $expired = [];
        foreach ([ActivityLog::VISIT, ActivityLog::LOGIN, ActivityLog::LOGOUT] as $event) {
            $log = new ActivityLog([
                'event' => $event,
                'route' => 'expired-' . $suffix,
                'created_at' => time() - 90 * 86400 - 10,
            ]);
            $I->assertTrue($log->save(false));
            $expired[] = $log->id;
        }
        $retained = new ActivityLog([
            'event' => ActivityLog::VISIT,
            'route' => 'retained-' . $suffix,
            'created_at' => time() - 90 * 86400 + 30,
        ]);
        $I->assertTrue($retained->save(false));
        $I->amOnRoute('site/index');
        foreach ($expired as $id) {
            $I->assertNull(ActivityLog::findOne($id));
        }
        $I->assertNotNull(ActivityLog::findOne($retained->id));

        $later = new ActivityLog([
            'event' => ActivityLog::VISIT,
            'route' => 'later-' . $suffix,
            'created_at' => time() - 91 * 86400,
        ]);
        $I->assertTrue($later->save(false));
        $I->amOnRoute('site/index');
        $I->assertNotNull(ActivityLog::findOne($later->id));
        $settings->logs_expiration_days = 30;
        $I->assertTrue($settings->save());
        $I->amOnRoute('site/index');
        $I->assertNull(ActivityLog::findOne($later->id));
        $I->assertNull(ActivityLog::findOne($retained->id));
    }

    public function retentionMigrationCanBeRetriedWithoutLosingSettings(FunctionalTester $I): void
    {
        require_once Yii::getAlias('@app/migrations/m261120_170000_add_activity_log_retention.php');
        $settings = ApplicationSettings::current();
        $settings->logs_expiration_days = 37;
        $I->assertTrue($settings->save());
        $migration = new \m261120_170000_add_activity_log_retention();
        $migration->safeUp();
        $migration->safeUp();
        $I->assertEquals(37, ApplicationSettings::current()->logs_expiration_days);
    }

    public function searchPaginationAndGranularPermissions(FunctionalTester $I): void
    {
        $admin = $this->user($I, 'admin');
        $staff = $this->user($I, 'staff');
        $suffix = bin2hex(random_bytes(4));
        $route = 'fixture-' . $suffix;
        $date = new \DateTimeImmutable('today', new \DateTimeZone(Yii::$app->formatter->timeZone ?: Yii::$app->timeZone));
        $timestamp = $date->setTime(12, 0)->getTimestamp();
        $logs = [];
        for ($i = 0; $i < 12; $i++) {
            $log = new ActivityLog([
                'user_id' => $staff->id,
                'event' => ActivityLog::VISIT,
                'route' => $route,
                'created_at' => $timestamp,
            ]);
            $I->assertTrue($log->save(false));
            $logs[] = $log;
        }

        $I->amLoggedInAs($admin);
        $I->amOnRoute('activity-log/index', [
            'ActivityLogSearch' => ['username' => $staff->username, 'route' => $route, 'user_id' => $staff->id],
            'event' => 'visit',
            'created_from' => $date->format('Y-m-d'),
            'created_to' => $date->format('Y-m-d'),
            'per-page' => 100,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeNumberOfElements('tbody tr[data-key]', 9);
        $I->see('1-9 of 12');
        $I->amOnRoute('activity-log/index', ['ActivityLogSearch' => ['id' => $logs[0]->id]]);
        $I->seeNumberOfElements('tbody tr[data-key]', 1);
        $I->amOnRoute('activity-log/view', ['id' => $logs[0]->id]);
        $I->seeResponseCodeIs(200);
        $I->see($staff->username);
        $I->see($route);
        $I->amOnRoute('admin-role/index');
        foreach (['activityLog.index', 'activityLog.view', 'notification.index', 'notification.read'] as $permission) {
            $I->seeElement('input[data-role="admin"][data-permission="' . $permission . '"]');
        }

        $I->amOnRoute('activity-log/index', ['ActivityLogSearch' => ['username' => ['invalid']]]);
        $I->seeResponseCodeIs(200);
        $I->see('Username must be a string');
        $I->dontSee($route);
        $I->amOnRoute('activity-log/index', ['created_from' => ['invalid']]);
        $I->seeResponseCodeIs(200);
        $I->dontSee($route);
        $search = new ActivityLogSearch();
        $provider = $search->search(['created_from' => '2024-02-01', 'created_to' => '2024-01-01']);
        $I->assertSame(0, $provider->getTotalCount());
        $I->assertTrue($search->filters->hasErrors('created_to'));

        $extra = new ActivityLog([
            'user_id' => $admin->id,
            'event' => ActivityLog::VISIT,
            'route' => $route,
            'created_at' => $timestamp,
        ]);
        $I->assertTrue($extra->save(false));
        foreach (['username' => $admin->id, '-username' => $staff->id] as $sort => $expectedUser) {
            $provider = (new ActivityLogSearch())->search(['ActivityLogSearch' => ['route' => $route]]);
            $provider->getSort()->params = ['sort' => $sort];
            $I->assertEquals($expectedUser, $provider->getModels()[0]->user_id);
        }

        $auth = Yii::$app->authManager;
        $role = $auth->createRole('activity_index_' . $suffix);
        $auth->add($role);
        $auth->addChild($role, $auth->getPermission('activityLog.index'));
        $auth->assign($role, $staff->id);
        $I->amLoggedInAs($staff);
        $I->amOnRoute('activity-log/index');
        $I->seeResponseCodeIs(200);
        $I->see('Activity logs');
        $I->dontSeeElement('tbody a.btn');
        $I->amOnRoute('activity-log/view', ['id' => $logs[0]->id]);
        $I->seeResponseCodeIs(403);
        $auth->addChild($role, $auth->getPermission('activityLog.view'));
        $I->amLoggedInAs($staff);
        $I->amOnRoute('activity-log/view', ['id' => $logs[0]->id]);
        $I->seeResponseCodeIs(200);
    }

    public function permissionMigrationGrantsExistingAndInheritedRoles(FunctionalTester $I): void
    {
        require_once Yii::getAlias('@app/migrations/m261120_150000_add_activity_notification_permissions.php');
        $auth = Yii::$app->authManager;
        $suffix = bin2hex(random_bytes(4));
        foreach (['admin', 'staff'] as $name) {
            $role = $auth->createRole('migration_' . $name . '_' . $suffix);
            $auth->add($role);
            $auth->addChild($role, $auth->getRole($name));
        }
        $migration = new \m261120_150000_add_activity_notification_permissions();
        $migration->safeUp();
        $migration->safeUp();

        foreach ($auth->getRoles() as $role) {
            $permissions = $auth->getPermissionsByRole($role->name);
            $I->assertArrayHasKey('notification.index', $permissions);
            $I->assertArrayHasKey('notification.read', $permissions);
        }
        foreach (['admin' => true, 'staff' => false] as $name => $expected) {
            $permissions = $auth->getPermissionsByRole('migration_' . $name . '_' . $suffix);
            $I->assertSame($expected, isset($permissions['activityLog.index']));
            $I->assertSame($expected, isset($permissions['activityLog.view']));
        }
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
