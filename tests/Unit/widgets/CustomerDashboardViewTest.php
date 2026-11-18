<?php

declare(strict_types=1);

namespace app\tests\Unit\widgets;

use Codeception\Test\Unit;
use Yii;
use yii\web\User;
use yii\web\View;

final class CustomerDashboardViewTest extends Unit
{
    public function testFeaturedProductsRequireProductViewPermission(): void
    {
        $user = $this->getMockBuilder(User::class)
            ->setConstructorArgs([['identityClass' => \app\models\User::class]])
            ->onlyMethods(['getIdentity', 'can'])
            ->getMock();
        $user->method('getIdentity')->willReturn(null);
        $user->method('can')->willReturn(false);
        Yii::$app->set('user', $user);

        $html = (new View())->renderFile(Yii::getAlias('@app/views/customer/dashboard.php'), [
            'featuredProducts' => ['not rendered'],
            'orderCount' => 0,
        ]);

        self::assertStringNotContainsString('Featured drinks and bites', $html);
    }
}
