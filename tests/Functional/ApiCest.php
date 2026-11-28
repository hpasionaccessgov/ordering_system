<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Category;
use app\models\Product;
use app\models\User;
use app\services\OrderService;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\rbac\DbManager;

final class ApiCest
{
    private User $customer;
    private string $password = 'ApiTest123!';
    private int $productId;

    public function _before(FunctionalTester $I): void
    {
        Yii::$app->set('authManager', DbManager::class);
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $this->customer = $this->createUser($I, 'api_customer_' . $suffix, $suffix, 'customer');
        $this->createUser($I, 'api_other_' . $suffix, 'other' . $suffix, 'customer');

        $category = new Category([
            'name' => 'API ' . $suffix,
            'status' => Category::STATUS_ACTIVE,
        ]);
        $I->assertTrue($category->save(), json_encode($category->getErrors()));
        $product = new Product([
            'name' => 'API product ' . $suffix,
            'category_id' => $category->id,
            'price' => '25.00',
            'status' => Product::STATUS_ACTIVE,
        ]);
        $I->assertTrue($product->save(), json_encode($product->getErrors()));
        $this->productId = (int) $product->id;
    }

    public function loginReturnsTokenAndProfileHidesSecrets(FunctionalTester $I): void
    {
        $token = $this->login($I, $this->customer->username);
        $I->assertNotEmpty($token);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
        $I->sendAjaxGetRequest('/index.php?r=api/auth/profile');
        $I->seeResponseCodeIs(200);
        $profile = $this->json($I)['data'];
        $I->assertSame($this->customer->username, $profile['username']);
        $I->assertArrayNotHasKey('password_hash', $profile);
        $I->assertArrayNotHasKey('auth_key', $profile);
    }

    public function unauthorizedRequestsAreRejected(FunctionalTester $I): void
    {
        $I->sendAjaxGetRequest('/index.php?r=api/order/index');
        $I->seeResponseCodeIs(401);
        $I->assertSame('unauthorized', $this->json($I)['error']['code']);

        $I->haveHttpHeader('Authorization', 'Bearer not-a-real-token');
        $I->sendAjaxGetRequest('/index.php?r=api/order/index');
        $I->seeResponseCodeIs(401);
    }

    public function catalogIsPublicAndPaginated(FunctionalTester $I): void
    {
        $I->sendAjaxGetRequest('/index.php?r=api/product/index');
        $I->seeResponseCodeIs(200);
        $products = $this->json($I);
        $I->assertTrue($products['success']);
        $I->assertNotEmpty($products['data']);
        $I->assertArrayHasKey('pagination', $products['meta']);

        $I->sendAjaxGetRequest('/index.php?r=api/product/view&id=' . $this->productId);
        $I->seeResponseCodeIs(200);
        $I->assertSame('API product', substr($this->json($I)['data']['name'], 0, 11));

        $I->sendAjaxGetRequest('/index.php?r=api/category/index');
        $I->seeResponseCodeIs(200);
        $I->assertArrayHasKey('pagination', $this->json($I)['meta']);
    }

    public function orderCreateListCancelUsesSameServiceAsCheckout(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->customer->username));
        $I->sendAjaxPostRequest('/index.php?r=api/order/create', [
            'items' => [['product_id' => $this->productId, 'quantity' => 2]],
        ]);
        $I->seeResponseCodeIs(201);
        $created = $this->json($I)['data'];
        $I->assertStringStartsWith('CB-', $created['order_number']);
        $I->assertSame('25.00', $created['items'][0]['unit_price']);
        $I->assertSame('50.00', $created['total_amount']);
        $I->assertSame('pending', $created['status']);
        $I->assertArrayNotHasKey('checkout_token', $created);

        $I->sendAjaxGetRequest('/index.php?r=api/order/index');
        $I->seeResponseCodeIs(200);
        $I->assertSame($created['id'], $this->json($I)['data'][0]['id']);

        $I->sendAjaxGetRequest('/index.php?r=api/order/view&id=' . $created['id']);
        $I->seeResponseCodeIs(200);
        $I->assertSame($created['order_number'], $this->json($I)['data']['order_number']);

        $I->sendAjaxPostRequest('/index.php?r=api/order/cancel&id=' . $created['id']);
        $I->seeResponseCodeIs(200);
        $I->assertSame('cancelled', $this->json($I)['data']['status']);
    }

    public function customersCannotReadOtherCustomersOrders(FunctionalTester $I): void
    {
        $order = (new OrderService())->create((int) $this->customer->id, [
            ['product_id' => $this->productId, 'quantity' => 1],
        ]);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, 'api_other_'));
        $I->sendAjaxGetRequest('/index.php?r=api/order/view&id=' . $order->id);
        $I->seeResponseCodeIs(404);
        $I->assertSame('not_found', $this->json($I)['error']['code']);
    }

    public function permissionFailuresReturnForbidden(FunctionalTester $I): void
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $user = $this->createUser($I, 'api_norole_' . $suffix, 'norole' . $suffix, null);
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $user->username));
        $I->sendAjaxPostRequest('/index.php?r=api/order/create', [
            'items' => [['product_id' => $this->productId, 'quantity' => 1]],
        ]);
        $I->seeResponseCodeIs(403);
        $I->assertSame('forbidden', $this->json($I)['error']['code']);
    }

    public function validationFailuresReturnJsonErrors(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->login($I, $this->customer->username));
        $I->sendAjaxPostRequest('/index.php?r=api/order/create', ['items' => []]);
        $I->seeResponseCodeIs(422);
        $error = $this->json($I)['error'];
        $I->assertSame('validation_failed', $error['code']);
        $I->assertNotEmpty($error['errors']['items']);
    }

    public function logoutRevokesThePresentedToken(FunctionalTester $I): void
    {
        $token = $this->login($I, $this->customer->username);
        $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
        $I->sendAjaxPostRequest('/index.php?r=api/auth/logout');
        $I->seeResponseCodeIs(200);

        $I->sendAjaxGetRequest('/index.php?r=api/auth/profile');
        $I->seeResponseCodeIs(401);
    }

    public function registerCreatesCustomerAndReturnsToken(FunctionalTester $I): void
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $username = 'api_reg_' . $suffix;
        $I->sendAjaxPostRequest('/index.php?r=api/auth/register', [
            'username' => $username,
            'email' => $suffix . '@register.test',
            'fullName' => 'Register ' . $suffix,
            'password' => $this->password,
            'passwordConfirm' => $this->password,
        ]);
        $I->seeResponseCodeIs(200);
        $token = (string) $this->json($I)['data']['token'];
        $I->assertNotEmpty($token);

        $user = User::findOne(['username' => $username]);
        $I->assertNotEmpty($user);
        $I->assertNotNull(Yii::$app->authManager->getAssignment('customer', (int) $user->id));
        $I->assertSame((int) $user->id, (int) $user->created_by);
        $I->assertSame((int) $user->id, (int) $user->updated_by);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
        $I->sendAjaxGetRequest('/index.php?r=api/auth/profile');
        $I->seeResponseCodeIs(200);
        $I->assertSame($username, $this->json($I)['data']['username']);
    }

    public function registerRejectsDuplicateAndInvalidInput(FunctionalTester $I): void
    {
        $suffix = substr(bin2hex(random_bytes(4)), 0, 8);
        $payload = [
            'username' => 'api_dup_' . $suffix,
            'email' => $suffix . '@dup.test',
            'fullName' => 'Duplicate ' . $suffix,
            'password' => $this->password,
            'passwordConfirm' => $this->password,
        ];
        $I->sendAjaxPostRequest('/index.php?r=api/auth/register', $payload);
        $I->seeResponseCodeIs(200);

        $payload['email'] = 'other' . $suffix . '@dup.test';
        $I->sendAjaxPostRequest('/index.php?r=api/auth/register', $payload);
        $I->seeResponseCodeIs(422);
        $I->assertSame('validation_failed', $this->json($I)['error']['code']);
        $I->assertNotEmpty($this->json($I)['error']['errors']['username']);

        $payload['username'] = 'api_bad_' . $suffix;
        $payload['password'] = 'short';
        $payload['passwordConfirm'] = 'short';
        $I->sendAjaxPostRequest('/index.php?r=api/auth/register', $payload);
        $I->seeResponseCodeIs(422);
        $I->assertNotEmpty($this->json($I)['error']['errors']['password']);
    }

    private function createUser(FunctionalTester $I, string $username, string $emailPart, ?string $role): User
    {
        $user = new User([
            'username' => $username,
            'email' => $emailPart . '@api.test',
            'full_name' => 'API test ' . $role,
            'status' => User::STATUS_ACTIVE,
        ]);
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $I->assertTrue($user->save(), json_encode($user->getErrors()));
        if ($role !== null) {
            Yii::$app->authManager->assign(Yii::$app->authManager->getRole($role), $user->id);
        }

        return $user;
    }

    private function login(FunctionalTester $I, string $usernamePrefix): string
    {
        $user = User::find()
            ->where(['like', 'username', $usernamePrefix . '%', false])
            ->one();
        $I->assertNotEmpty($user);
        $I->sendAjaxPostRequest('/index.php?r=api/auth/login', [
            'username' => $user->username,
            'password' => $this->password,
        ]);
        $I->seeResponseCodeIs(200);

        return (string) $this->json($I)['data']['token'];
    }

    /**
     * @return array<string, mixed>
     */
    private function json(FunctionalTester $I): array
    {
        return json_decode($I->grabPageSource(), true, 512, JSON_THROW_ON_ERROR);
    }
}
