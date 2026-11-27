<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\ActivityLog;
use app\models\ApplicationSettings;
use app\models\Category;
use app\models\Favorite;
use app\models\Inventory;
use app\models\InventoryTransaction;
use app\models\LoyaltyPointTransaction;
use app\models\Notification;
use app\models\Order;
use app\models\OrderItem;
use app\models\OrderStatusHistory;
use app\models\Product;
use app\models\ProductIngredient;
use app\models\ProductReview;
use app\models\Reward;
use app\models\User;
use Codeception\Test\Unit;
use yii\behaviors\BlameableBehavior;

final class AuditColumnsTest extends Unit
{
    private const MODELS = [
        ActivityLog::class,
        ApplicationSettings::class,
        Category::class,
        Favorite::class,
        Inventory::class,
        InventoryTransaction::class,
        LoyaltyPointTransaction::class,
        Notification::class,
        Order::class,
        OrderItem::class,
        OrderStatusHistory::class,
        Product::class,
        ProductIngredient::class,
        ProductReview::class,
        Reward::class,
        User::class,
    ];

    public function testAllApplicationTablesHaveAuditColumns(): void
    {
        foreach (self::MODELS as $modelClass) {
            $columns = $modelClass::getTableSchema()->columns;
            self::assertArrayHasKey('created_by', $columns, $modelClass);
            self::assertArrayHasKey('updated_by', $columns, $modelClass);
        }
    }

    public function testAllAuditedModelsAttachBlameableBehavior(): void
    {
        foreach (self::MODELS as $modelClass) {
            $found = false;
            foreach ((new $modelClass())->behaviors() as $behavior) {
                $behaviorClass = is_array($behavior) ? ($behavior['class'] ?? null) : $behavior;
                if ($behaviorClass === BlameableBehavior::class) {
                    $found = true;
                    break;
                }
            }
            self::assertTrue($found, $modelClass);
        }
    }
}
