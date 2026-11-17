<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\OrderItem;
use Codeception\Test\Unit;
use yii\behaviors\TimestampBehavior;

final class OrderItemTest extends Unit
{
    public function testCreatedAtIsManagedWithoutAnUpdatedAtColumn(): void
    {
        $item = new OrderItem();
        $behaviors = $item->behaviors();

        self::assertArrayHasKey(0, $behaviors);
        self::assertSame(TimestampBehavior::class, $behaviors[0]['class']);
        self::assertFalse($behaviors[0]['updatedAtAttribute']);
    }
}
