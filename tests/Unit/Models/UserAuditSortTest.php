<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\User;
use Codeception\Test\Unit;
use yii\db\Expression;

final class UserAuditSortTest extends Unit
{
    public function testSelfReferencingAuditSortUsesAnInnerAlias(): void
    {
        $expression = User::usernameSortExpression('{{%users}}', 'updated_by', SORT_ASC);

        self::assertInstanceOf(Expression::class, $expression);
        self::assertStringContainsString('AS `audit_user`', (string) $expression);
        self::assertStringContainsString(
            '`audit_user`.`id` = `users`.`updated_by`',
            (string) $expression,
        );
    }
}
