<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use Codeception\Test\Unit;

final class RewardRedemptionTest extends Unit
{
    public function testRewardMigrationLinksRewardsToMenuProducts(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 3) . '/migrations/m261127_120000_link_rewards_to_products.php');

        self::assertStringContainsString("'product_id'", $migration);
        self::assertStringContainsString("'{{%products}}'", $migration);
        self::assertStringContainsString("'Free Coffee', 'Cafebara House Latte'", $migration);
    }

    public function testRewardModelRequiresAnActiveProduct(): void
    {
        $model = (string) file_get_contents(dirname(__DIR__, 3) . '/models/Reward.php');

        self::assertStringContainsString("[['name', 'point_cost', 'product_id'], 'required']", $model);
        self::assertStringContainsString("'targetClass' => Product::class", $model);
        self::assertStringContainsString('public function isRedeemable', $model);
        self::assertStringContainsString('Product::STATUS_ACTIVE', $model);
    }

    public function testRedemptionCreatesAFreeLinkedOrder(): void
    {
        $service = (string) file_get_contents(dirname(__DIR__, 3) . '/services/LoyaltyService.php');

        self::assertStringContainsString('new OrderService()', $service);
        self::assertStringContainsString("'product_id' => (int) \$freshReward->product_id", $service);
        self::assertStringContainsString("'order_id' => \$order->id", $service);
        self::assertStringContainsString('Navigation::ORDER_VIEW', $service);
    }

    public function testOrderServiceCanOverrideItemPriceForRewards(): void
    {
        $service = (string) file_get_contents(dirname(__DIR__, 3) . '/services/OrderService.php');

        self::assertStringContainsString('?float $unitPriceOverride = null', $service);
        self::assertStringContainsString('$unitPriceOverride ?? (float) $product->price', $service);
    }

    public function testRewardFormOffersMenuProducts(): void
    {
        $form = (string) file_get_contents(dirname(__DIR__, 3) . '/views/reward/_form.php');

        self::assertStringContainsString("\$form->field(\$model, 'product_id')->dropDownList(\$products", $form);
    }

    public function testRewardCardUsesMappedProductImage(): void
    {
        $view = (string) file_get_contents(dirname(__DIR__, 3) . '/views/loyalty/index.php');
        $css = (string) file_get_contents(dirname(__DIR__, 3) . '/web/css/cafebara-theme.css');

        self::assertStringContainsString('$rewardProduct?->getImageUrl()', $view);
        self::assertStringContainsString('cafebara-reward-image', $view);
        self::assertStringContainsString('bi bi-cup-hot', $view);
        self::assertStringContainsString('.cafebara-reward-image', $css);
    }

    public function testStaffCanCreateRewards(): void
    {
        $migration = (string) file_get_contents(dirname(__DIR__, 3) . '/migrations/m261127_130000_grant_staff_reward_permissions.php');

        self::assertStringContainsString("getRole('staff')", $migration);
        foreach (['reward.index', 'reward.view', 'reward.create', 'reward.update'] as $permission) {
            self::assertStringContainsString("'" . $permission . "'", $migration);
        }
    }
}
