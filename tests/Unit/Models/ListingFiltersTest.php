<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Category;
use app\models\CategorySearch;
use app\models\Order;
use app\models\OrderSearch;
use app\models\Product;
use app\models\ProductSearch;
use app\models\User;
use app\models\UserSearch;
use Codeception\Test\Unit;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\rbac\DbManager;

final class ListingFiltersTest extends Unit
{
    public function testCategoryStatusAuditAndDateFilters(): void
    {
        $actor = $this->user();
        $suffix = bin2hex(random_bytes(4));
        $zone = new DateTimeZone('Asia/Manila');
        $start = (new DateTimeImmutable('2026-03-10 00:00:00', $zone))->getTimestamp();
        $categories = [];
        foreach ([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE] as $status) {
            $category = new Category(['name' => 'filter_' . $suffix . '_' . $status, 'status' => $status]);
            self::assertTrue($category->save(), json_encode($category->getErrors()));
            Category::updateAll([
                'created_by' => $actor->id, 'updated_by' => $actor->id,
                'created_at' => $start, 'updated_at' => $start,
            ], ['id' => $category->id]);
            $categories[] = $category;
        }
        $search = new CategorySearch();
        $provider = $search->search([
            'q' => $suffix,
            'CategorySearch' => ['status' => '0'],
            'created_by_name' => substr($actor->username, 0, 10),
            'updated_by_name' => $actor->username,
            'created_from' => '2026-03-10',
            'created_to' => '2026-03-10',
        ]);
        self::assertSame([(int) $categories[1]->id], array_map(static fn(Category $item): int => (int) $item->id, $provider->getModels()));
        self::assertSame(0, (new CategorySearch())->search([
            'q' => $suffix, 'created_to' => '2026-03-09',
        ])->getTotalCount());
    }

    public function testOrderTotalsAndAuditFiltersRespectOwnership(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $suffix = bin2hex(random_bytes(4));
        foreach ([[$owner, '10.00'], [$owner, '20.00'], [$other, '20.00']] as $index => [$user, $amount]) {
            $order = new Order([
                'user_id' => $user->id, 'order_number' => 'FILTER-' . $suffix . '-' . $index,
                'status' => Order::STATUS_PENDING, 'total_amount' => $amount,
            ]);
            self::assertTrue($order->save(), json_encode($order->getErrors()));
            Order::updateAll(['created_by' => $other->id], ['id' => $order->id]);
        }
        $params = ['q' => $suffix, 'min_amount' => '15', 'max_amount' => '20', 'created_by_name' => 'missing_user'];
        $customer = new OrderSearch();
        self::assertSame(1, $customer->search($params, $owner->id, false)->getTotalCount());
        self::assertSame('', $customer->filters->created_by_name);
        self::assertSame(0, (new OrderSearch())->search($params, $owner->id, true)->getTotalCount());
        $params['created_by_name'] = $other->username;
        self::assertSame(2, (new OrderSearch())->search($params, $owner->id, true)->getTotalCount());
    }

    public function testProductPricesAndInternalAuditStayRestricted(): void
    {
        $actor = $this->user();
        $suffix = bin2hex(random_bytes(4));
        $category = new Category(['name' => 'product_filter_' . $suffix, 'status' => Category::STATUS_ACTIVE]);
        self::assertTrue($category->save());
        foreach ([[Product::STATUS_ACTIVE, '5.00'], [Product::STATUS_ACTIVE, '20.00'], [Product::STATUS_ARCHIVED, '20.00']] as $index => [$status, $price]) {
            $product = new Product(['name' => $suffix . '_' . $index, 'category_id' => $category->id, 'status' => $status, 'price' => $price]);
            self::assertTrue($product->save(), json_encode($product->getErrors()));
            Product::updateAll(['created_by' => $actor->id], ['id' => $product->id]);
        }
        $params = ['q' => $suffix, 'min_amount' => '10', 'max_amount' => '20', 'created_by_name' => 'not_a_user'];
        self::assertSame(0, (new ProductSearch())->search($params, true, true)->getTotalCount());
        self::assertSame(1, (new ProductSearch())->search($params, true, false)->getTotalCount());
        self::assertSame(1, (new ProductSearch())->searchMenu($params)->getTotalCount());
        self::assertSame(1, (new ProductSearch())->searchMenu(['q' => $suffix, 'min_price' => '10'])->getTotalCount());
    }

    public function testUserStatusRoleAndInvalidRanges(): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $user = $this->user();
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole('customer'), $user->id);
        $search = new UserSearch();
        self::assertSame(1, $search->search(['q' => $user->username, 'status' => '10', 'role' => 'customer'])->getTotalCount());
        self::assertSame(0, (new UserSearch())->search(['q' => $user->username, 'status' => '0'])->getTotalCount());
        $orderSearch = new OrderSearch();
        self::assertSame(0, $orderSearch->search(['min_amount' => '20', 'max_amount' => '10'], $user->id, false)->getTotalCount());
        self::assertTrue($orderSearch->filters->hasErrors('max_amount'));
        $orderSearch = new OrderSearch();
        self::assertSame(0, $orderSearch->search(['created_from' => 'invalid'], $user->id, false)->getTotalCount());
        self::assertTrue($orderSearch->filters->hasErrors('created_from'));
        $orderSearch = new OrderSearch();
        self::assertSame(0, $orderSearch->search([
            'min_amount' => ['invalid'], 'max_amount' => '10',
            'created_from' => ['invalid'], 'created_to' => '2026-03-10',
        ], $user->id, false)->getTotalCount());
        self::assertTrue($orderSearch->filters->hasErrors('min_amount'));
        self::assertTrue($orderSearch->filters->hasErrors('created_from'));
    }

    private function user(): User
    {
        $name = 'filter_' . bin2hex(random_bytes(4));
        $user = new User(['username' => $name, 'email' => $name . '@example.test', 'full_name' => 'Filter User', 'status' => User::STATUS_ACTIVE]);
        $user->setPassword('FilterTest123!');
        $user->generateAuthKey();
        self::assertTrue($user->save(), json_encode($user->getErrors()));

        return $user;
    }
}
