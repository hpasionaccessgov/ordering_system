<?php

declare(strict_types=1);

namespace app\tests\Unit\widgets;

use app\models\Product;
use Codeception\Test\Unit;
use Yii;
use yii\web\User as WebUser;
use yii\web\View;

final class ProductAuditViewTest extends Unit
{
    public function testAuditSummaryAppearsOnlyForProductManagers(): void
    {
        Yii::$app->formatter->currencyCode = 'PHP';
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['__get'])
            ->getMock();
        $attributes = [
            'id' => 10,
            'name' => 'Audit product',
            'description' => '',
            'price' => '10.00',
            'status' => Product::STATUS_ACTIVE,
            'created_at' => 1700000000,
            'updated_at' => 1700000100,
            'image_data' => null,
            'image_path' => null,
            'categoryRecord' => null,
            'createdBy' => (object) ['username' => 'creator'],
            'updatedBy' => (object) ['username' => 'updater'],
        ];
        $product->method('__get')->willReturnCallback(
            static fn(string $name) => $attributes[$name] ?? null,
        );

        $manager = $this->user(['product.update', 'product.view']);
        Yii::$app->set('user', $manager);
        $managerHtml = (new View())->renderFile(
            Yii::getAlias('@app/views/shared/_product_card.php'),
            ['product' => $product],
        );
        self::assertStringContainsString('Created by', $managerHtml);
        self::assertStringContainsString('creator', $managerHtml);
        self::assertStringContainsString('updater', $managerHtml);

        Yii::$app->set('user', $this->user(['product.view']));
        $viewerHtml = (new View())->renderFile(
            Yii::getAlias('@app/views/shared/_product_card.php'),
            ['product' => $product],
        );
        self::assertStringNotContainsString('Created by', $viewerHtml);
    }

    /**
     * @param list<string> $permissions
     */
    private function user(array $permissions): WebUser
    {
        $user = $this->getMockBuilder(WebUser::class)
            ->setConstructorArgs([['identityClass' => \app\models\User::class]])
            ->onlyMethods(['getIsGuest', 'can'])
            ->getMock();
        $user->method('getIsGuest')->willReturn(false);
        $user->method('can')->willReturnCallback(
            static fn(string $permission): bool => in_array($permission, $permissions, true),
        );

        return $user;
    }
}
