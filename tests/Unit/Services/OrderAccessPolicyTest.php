<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Order;
use app\services\OrderAccessPolicy;
use Codeception\Test\Unit;
use yii\db\ActiveQuery;

final class OrderAccessPolicyTest extends Unit
{
    public function testCustomerOrderQueryIsRestrictedToTheirOwnRecords(): void
    {
        $query = (new ActiveQuery(Order::class))->where(['id' => 12]);

        OrderAccessPolicy::scopeToOwner($query, 41, false);

        self::assertSame([
            'and',
            ['id' => 12],
            ['user_id' => 41],
        ], $query->where);
    }

    public function testOnlyAdminOrStaffCanViewAllOrders(): void
    {
        self::assertFalse(OrderAccessPolicy::canViewAllOrders(false, false));
        self::assertTrue(OrderAccessPolicy::canViewAllOrders(false, true));
        self::assertTrue(OrderAccessPolicy::canViewAllOrders(true, false));
    }
}
