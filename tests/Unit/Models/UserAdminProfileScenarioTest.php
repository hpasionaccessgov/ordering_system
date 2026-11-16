<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\User;
use Codeception\Test\Unit;

final class UserAdminProfileScenarioTest extends Unit
{
    public function testAdminProfileUpdateOnlyAllowsPersonalDataAttributes(): void
    {
        $user = new User(['scenario' => User::SCENARIO_ADMIN_PROFILE_UPDATE]);

        self::assertSame(['username', 'email', 'full_name', 'status'], $user->safeAttributes());
    }
}
