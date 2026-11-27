<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\controllers\CategoryController;
use app\controllers\OrderController;
use app\controllers\ProductController;
use app\controllers\SiteController;
use app\controllers\StaffController;
use Codeception\Test\Unit;
use Yii;
use yii\base\Action;
use yii\rbac\DbManager;
use yii\rbac\Role;
use yii\web\ForbiddenHttpException;
use yii\web\User;
use yii\web\View;

final class CategoryAccessTest extends Unit
{
    public function testEachCategoryActionRequiresItsPermission(): void
    {
        $controller = new CategoryController('category', Yii::$app);
        $filter = $controller->getBehavior('access');

        foreach (['index', 'view', 'create', 'update', 'delete'] as $actionName) {
            $user = $this->getMockBuilder(User::class)
                ->setConstructorArgs([['identityClass' => \app\models\User::class]])
                ->onlyMethods(['getIsGuest', 'can'])
                ->getMock();
            $user->method('getIsGuest')->willReturn(false);
            $user->method('can')->willReturnCallback(
                static fn(string $permission): bool => $permission === 'category.' . $actionName,
            );
            Yii::$app->set('user', $user);
            $filter->user = $user;

            self::assertTrue($filter->beforeAction(new Action($actionName, $controller)));

            $otherAction = $actionName === 'index' ? 'delete' : 'index';
            try {
                $filter->beforeAction(new Action($otherAction, $controller));
                self::fail("{$otherAction} should be denied without its permission.");
            } catch (ForbiddenHttpException $exception) {
                self::assertSame(403, $exception->statusCode);
            }
        }
    }

    public function testBuiltInAndCustomStaffFollowTheSamePermissionsWithoutStaffAccess(): void
    {
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->request->setScriptUrl('/index.php');

        foreach (['staff', 'custom-staff'] as $roleName) {
            $user = $this->getMockBuilder(User::class)
                ->setConstructorArgs([['identityClass' => \app\models\User::class]])
                ->onlyMethods(['getIsGuest', 'getId', 'can'])
                ->getMock();
            $user->method('getIsGuest')->willReturn(false);
            $user->method('getId')->willReturn(1);
            $user->method('can')->willReturnCallback(
                static fn(string $permission): bool => in_array(
                    $permission,
                    ['category.index', 'product.index', 'order.index'],
                    true,
                ),
            );
            Yii::$app->set('user', $user);

            $auth = $this->getMockBuilder(DbManager::class)
                ->onlyMethods(['getRolesByUser'])
                ->getMock();
            $auth->method('getRolesByUser')->willReturn([$roleName => new Role(['name' => $roleName])]);
            Yii::$app->set('authManager', $auth);

            $category = new CategoryController('category', Yii::$app);
            Yii::$app->controller = $category;
            self::assertTrue($category->beforeAction(new Action('index', $category)));
            $product = new ProductController('product', Yii::$app);
            self::assertTrue($product->beforeAction(new Action('index', $product)));
            $order = new OrderController('order', Yii::$app);
            self::assertTrue($order->beforeAction(new Action('index', $order)));

            $site = Yii::createObject(SiteController::class, ['site', Yii::$app]);
            self::assertTrue($site->beforeAction(new Action('profile', $site)));

            $view = new View(['context' => Yii::$app->controller]);
            $sidebar = $view->renderFile(Yii::getAlias('@app/views/layouts/_sidebar.php'));
            self::assertStringContainsString('Categories', $sidebar);
            self::assertStringNotContainsString('Profile', $sidebar);
            self::assertStringContainsString('<p>Products</p>', $sidebar);
            self::assertStringContainsString('<p>Orders</p>', $sidebar);
            self::assertStringNotContainsString('<p>Roles</p>', $sidebar);
            self::assertStringNotContainsString('<p>Dashboard</p>', $sidebar);

            $header = $view->renderFile(Yii::getAlias('@app/views/layouts/_header.php'));
            self::assertStringContainsString('Profile', $header);
            self::assertStringContainsString('Sign out', $header);

            foreach ([
                [$category, 'create'],
                [$product, 'create'],
                [$order, 'create'],
                [new StaffController('staff', Yii::$app), 'index'],
            ] as [$controller, $action]) {
                try {
                    $controller->beforeAction(new Action($action, $controller));
                    self::fail("{$roleName} should not access {$action} without its permission.");
                } catch (ForbiddenHttpException $exception) {
                    self::assertSame(403, $exception->statusCode);
                }
            }
        }
    }
}
