<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\ActivityLogSearch;
use app\models\ApplicationSettings;
use app\models\CategorySearch;
use app\models\OrderSearch;
use app\models\ProductSearch;
use app\models\User;
use app\models\UserSearch;
use app\services\SessionUser;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class SettingsCest
{
    public function _before(): void
    {
        Yii::$app->set('authManager', DbManager::class);
    }

    public function adminsCanSaveValidatedSettings(FunctionalTester $I): void
    {
        $admin = $this->user($I, 'admin');
        $I->amLoggedInAs($admin);
        $I->amOnRoute('settings/index');
        $I->seeResponseCodeIs(200);
        $I->see('Settings');
        $I->amOnRoute('admin-role/index');
        $I->seeElement('input[data-role="admin"][data-permission="settings.index"]');
        $I->seeElement('input[data-role="admin"][data-permission="settings.update"]');
        $I->amOnRoute('settings/index');
        $I->submitForm('form[action*="settings%2Fupdate"]', [
            'ApplicationSettings[card_page_size]' => '3',
            'ApplicationSettings[table_page_size]' => '4',
            'ApplicationSettings[idle_minutes]' => '15',
            'ApplicationSettings[login_max_attempts]' => '3',
            'ApplicationSettings[login_reset_minutes]' => '2',
            'ApplicationSettings[login_lock_minutes]' => '5',
            'ApplicationSettings[logs_expiration_days]' => '45',
            'ApplicationSettings[notifications_expiration_days]' => '30',
        ]);
        $I->see('Settings saved.');
        $settings = ApplicationSettings::current();
        $I->assertEquals(3, $settings->card_page_size);
        $I->assertEquals(4, $settings->table_page_size);
        $I->assertEquals(15, $settings->idle_minutes);
        $I->assertEquals(5, $settings->login_lock_minutes);
        $I->assertEquals(45, $settings->logs_expiration_days);

        $I->amOnRoute('settings/index');
        $I->submitForm('form[action*="settings%2Fupdate"]', [
            'ApplicationSettings[card_page_size]' => '0',
            'ApplicationSettings[table_page_size]' => '101',
            'ApplicationSettings[idle_minutes]' => '15',
            'ApplicationSettings[login_max_attempts]' => '3',
            'ApplicationSettings[login_reset_minutes]' => '2',
            'ApplicationSettings[login_lock_minutes]' => '5',
            'ApplicationSettings[logs_expiration_days]' => '0',
            'ApplicationSettings[notifications_expiration_days]' => '30',
        ]);
        $I->see('must be no less than 1');
        $I->assertEquals(3, ApplicationSettings::current()->card_page_size);
        $I->assertEquals(45, ApplicationSettings::current()->logs_expiration_days);
    }

    public function paginationUsesConfiguredLimits(FunctionalTester $I): void
    {
        $settings = ApplicationSettings::current();
        $settings->card_page_size = 3;
        $settings->table_page_size = 4;
        $I->assertTrue($settings->save());
        Yii::$app->request->setQueryParams(['per-page' => 100]);

        $providers = [
            (new CategorySearch())->search([]),
            (new UserSearch())->search([]),
            (new OrderSearch())->search([], 1, true),
            (new ActivityLogSearch())->search([]),
        ];
        foreach ($providers as $provider) {
            $I->assertSame(4, $provider->getPagination()->getPageSize());
        }
        $I->assertSame(3, (new ProductSearch())->searchMenu([])->getPagination()->getPageSize());
        $I->assertSame(3, (new ProductSearch())->search([], true, true)->getPagination()->getPageSize());
    }

    public function logRetentionRejectsInvalidValues(FunctionalTester $I): void
    {
        $settings = ApplicationSettings::current();
        foreach ([0, 3651, 'invalid', ['90']] as $value) {
            $settings->logs_expiration_days = $value;
            $I->assertFalse($settings->validate(['logs_expiration_days']));
            $I->assertTrue($settings->hasErrors('logs_expiration_days'));
        }
        $admin = $this->user($I, 'admin');
        $I->amLoggedInAs($admin);
        $I->sendAjaxPostRequest('/index.php?r=settings%2Fupdate', [
            'ApplicationSettings' => ['logs_expiration_days' => ['invalid']],
        ]);
        $I->seeResponseCodeIs(200);
        $I->assertStringContainsString('Activity log retention (days) must be an integer.', $I->grabPageSource());

        $settings->notifications_expiration_days = 30;
        foreach ([0, 3651, 'invalid', ['30']] as $value) {
            $settings->notifications_expiration_days = $value;
            $I->assertFalse($settings->validate(['notifications_expiration_days']));
            $I->assertTrue($settings->hasErrors('notifications_expiration_days'));
        }
    }

    public function settingsPermissionsAreEnforced(FunctionalTester $I): void
    {
        $staff = $this->user($I, 'staff');
        $I->amLoggedInAs($staff);
        $I->amOnRoute('settings/index');
        $I->seeResponseCodeIs(403);
        $I->sendAjaxPostRequest('/index.php?r=settings%2Fupdate', ['ApplicationSettings' => ['card_page_size' => 1]]);
        $I->seeResponseCodeIs(403);

        $auth = Yii::$app->authManager;
        $role = $auth->createRole('settings_view_' . bin2hex(random_bytes(4)));
        $auth->add($role);
        $auth->addChild($role, $auth->getPermission('settings.index'));
        $auth->assign($role, $staff->id);
        $I->amLoggedInAs($staff);
        $I->amOnRoute('settings/index');
        $I->seeResponseCodeIs(200);
        $I->dontSee('Save settings');
        $I->sendAjaxPostRequest('/index.php?r=settings%2Fupdate', ['ApplicationSettings' => ['card_page_size' => 1]]);
        $I->seeResponseCodeIs(403);
    }

    public function backgroundPollingDoesNotRenewAnIdleSession(FunctionalTester $I): void
    {
        $user = $this->user($I, 'customer');
        $I->amLoggedInAs($user);
        $lastActivity = time() - 20;
        Yii::$app->session->set('__last_user_activity', $lastActivity);
        $this->refreshSessionUser();
        $I->amOnRoute('session/status');
        $I->see('"authenticated":true');
        $I->assertSame($lastActivity, Yii::$app->session->get('__last_user_activity'));
        $this->refreshSessionUser();
        $I->amOnRoute('notification/unread-count');
        $I->seeResponseCodeIs(200);
        $I->assertSame($lastActivity, Yii::$app->session->get('__last_user_activity'));
        $this->refreshSessionUser();
        $I->sendAjaxPostRequest('/index.php?r=session%2Factivity');
        $I->see('"authenticated":true');
        $I->assertGreaterThan($lastActivity, Yii::$app->session->get('__last_user_activity'));

        Yii::$app->session->set('__last_user_activity', time() - 1801);
        $this->refreshSessionUser();
        $I->amOnRoute('session/status');
        $I->see('"authenticated":false');
    }

    public function migrationPreservesSettingsAndGrantsInheritedAdmins(FunctionalTester $I): void
    {
        require_once Yii::getAlias('@app/migrations/m261120_160000_create_application_settings.php');
        $settings = ApplicationSettings::current();
        $settings->table_page_size = 7;
        $I->assertTrue($settings->save());
        $auth = Yii::$app->authManager;
        $suffix = bin2hex(random_bytes(4));
        foreach (['admin', 'staff'] as $name) {
            $role = $auth->createRole('settings_migration_' . $name . '_' . $suffix);
            $auth->add($role);
            $auth->addChild($role, $auth->getRole($name));
        }
        $migration = new \m261120_160000_create_application_settings();
        $migration->safeUp();
        $migration->safeUp();
        $I->assertEquals(7, ApplicationSettings::current()->table_page_size);
        foreach (['admin' => true, 'staff' => false] as $name => $expected) {
            $permissions = $auth->getPermissionsByRole('settings_migration_' . $name . '_' . $suffix);
            $I->assertSame($expected, isset($permissions['settings.index']));
            $I->assertSame($expected, isset($permissions['settings.update']));
        }
    }

    private function refreshSessionUser(): void
    {
        Yii::$app->set('user', ['class' => SessionUser::class, 'identityClass' => User::class]);
    }

    private function user(FunctionalTester $I, string $role): User
    {
        $name = $role . '_' . bin2hex(random_bytes(4));
        $user = new User([
            'username' => $name,
            'email' => $name . '@example.test',
            'full_name' => 'Settings test',
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword('SettingsTest123!');
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole($role), $user->id);
        return $user;
    }
}
