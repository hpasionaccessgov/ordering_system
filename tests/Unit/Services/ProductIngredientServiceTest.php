<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\exceptions\InsufficientInventoryException;
use Codeception\Test\Unit;

final class ProductIngredientServiceTest extends Unit
{
    public function testShortageMessageListsEveryItem(): void
    {
        $exception = new InsufficientInventoryException([
            ['name' => 'Milk', 'needed' => 300, 'available' => 200],
            ['name' => 'Cup', 'needed' => 2, 'available' => 1],
        ]);

        self::assertSame(
            "Cannot complete order.\n\nInsufficient inventory:\n"
            . "- Milk needs 300, available 200\n- Cup needs 2, available 1",
            $exception->getMessage(),
        );
    }
}
