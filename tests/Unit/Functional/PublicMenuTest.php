<?php

declare(strict_types=1);

namespace app\tests\Unit\Functional;

use app\controllers\SiteController;
use Codeception\Test\Unit;
use Yii;
use yii\mail\MailerInterface;
use yii\base\Security;
use yii\web\User;

final class PublicMenuTest extends Unit
{
    public function testMenuShowsOnlyActiveProductsAndUsesFixedPagination(): void
    {
        $guest = $this->getMockBuilder(User::class)
            ->setConstructorArgs([['identityClass' => \app\models\User::class]])
            ->onlyMethods(['getIsGuest', 'can'])
            ->getMock();
        $guest->method('getIsGuest')->willReturn(true);
        $guest->method('can')->willReturn(false);
        Yii::$app->set('user', $guest);
        Yii::$app->request->setQueryParams([
            'q' => 'coffee',
            'category_id' => 'invalid',
        ]);

        $controller = $this->getMockBuilder(SiteController::class)
            ->setConstructorArgs([
                'site',
                Yii::$app,
                Yii::createObject(MailerInterface::class),
                new Security(),
            ])
            ->onlyMethods(['render'])
            ->getMock();
        $controller->expects(self::once())
            ->method('render')
            ->willReturnCallback(static function (string $view, array $params): string {
                self::assertSame('menu', $view);
                self::assertSame(
                    ['status' => \app\models\Product::STATUS_ACTIVE],
                    $params['dataProvider']->query->where[1],
                );
                self::assertSame(8, $params['dataProvider']->pagination->getPageSize());
                self::assertSame('coffee', $params['search']);
                self::assertSame('invalid', $params['categoryId']);

                return 'rendered';
            });

        self::assertSame('rendered', $controller->actionMenu());
    }
}
