<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\RoleForm;
use Codeception\Test\Unit;

final class RoleFormTest extends Unit
{
    public function testInvalidRoleNameShowsFieldErrorInsteadOfAcceptingInput(): void
    {
        $model = new RoleForm(['name' => 'Cashier role']);

        self::assertFalse($model->validate());
        self::assertNotEmpty($model->getErrors('name'));
    }
}
