<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\ApplicationSettings;
use app\models\Order;
use app\modules\api\models\OrderCreateForm;
use app\services\OrderAccessPolicy;
use app\services\OrderService;
use app\services\OrderStatusService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

final class OrderController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['create'], 'allow' => true, 'roles' => ['cart.checkout']],
                ['actions' => ['index', 'view'], 'allow' => true, 'roles' => ['order.view']],
                ['actions' => ['cancel'], 'allow' => true, 'roles' => ['order.cancel']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
            'view' => ['GET'],
            'create' => ['POST'],
            'cancel' => ['POST'],
        ];
    }

    public function actionIndex(): array
    {
        $provider = new ActiveDataProvider([
            'query' => $this->orderQuery()->with('items')->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ApplicationSettings::pagination(),
        ]);

        $orders = array_map(
            fn(Order $order): array => $this->serializeOrder($order),
            $provider->getModels(),
        );

        return $this->ok($orders, ['pagination' => $this->paginationMeta($provider->getPagination())]);
    }

    public function actionView(int $id): array
    {
        return $this->ok($this->serializeOrder($this->findOrder($id)));
    }

    public function actionCreate(): array
    {
        $form = new OrderCreateForm();
        $form->load(Yii::$app->request->getBodyParams(), '');
        if (!$form->validate()) {
            $this->fail(422, 'validation_failed', 'Please correct the errors below.', $form->getErrors());
        }

        $order = (new OrderService())->create((int) Yii::$app->user->id, $form->orderItems());

        return $this->ok($this->serializeOrder($order), [], 201);
    }

    public function actionCancel(int $id): array
    {
        $order = $this->findOrder($id);
        $order = (new OrderStatusService())->cancel(
            $order,
            (int) Yii::$app->user->id,
            (bool) Yii::$app->user->can('order.process'),
        );

        return $this->ok($this->serializeOrder($order));
    }

    private function orderQuery(): \yii\db\ActiveQuery
    {
        return OrderAccessPolicy::scopeToOwner(
            Order::find(),
            Yii::$app->user->id,
            $this->canViewAllOrders(),
        );
    }

    private function findOrder(int $id): Order
    {
        $order = $this->orderQuery()->with('items')->andWhere(['id' => $id])->one();
        if ($order === null) {
            $this->fail(404, 'not_found', 'Order not found.');
        }

        return $order;
    }

    private function canViewAllOrders(): bool
    {
        return OrderAccessPolicy::canViewAllOrders(
            (bool) Yii::$app->user->can('admin.access'),
            (bool) Yii::$app->user->can('staff.access'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeOrder(Order $order): array
    {
        $data = $order->toArray([
            'id',
            'order_number',
            'status',
            'total_amount',
            'created_at',
            'updated_at',
            'confirmed_at',
            'processing_at',
            'ready_at',
            'completed_at',
        ]);
        $data['items'] = array_map(
            static fn($item): array => $item->toArray([
                'id',
                'product_id',
                'product_name',
                'quantity',
                'unit_price',
                'subtotal',
            ]),
            $order->items,
        );

        return $data;
    }
}
