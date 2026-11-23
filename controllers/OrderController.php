<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Order;
use app\models\Notification;
use app\models\OrderSearch;
use app\services\ShoppingCart;
use app\services\OrderAccessPolicy;
use app\services\OrderService;
use app\services\OrderStatusService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Controller;
use app\models\Product;
use yii\web\BadRequestHttpException;

/**
 * Controller for managing orders.
 */
class OrderController extends Controller
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['order.index'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['order.view'],
                    ],
                    [
                        'actions' => ['confirmation'],
                        'allow' => true,
                        'roles' => ['order.view'],
                    ],
                    [
                        'actions' => ['confirmation'],
                        'allow' => true,
                        'roles' => ['cart.checkout'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['order.create'],
                    ],
                    [
                        'actions' => ['create-product'],
                        'allow' => true,
                        'roles' => ['order.create'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['order.update'],
                    ],
                    [
                        'actions' => ['cancel'],
                        'allow' => true,
                        'roles' => ['order.cancel'],
                    ],
                    [
                        'actions' => ['process'],
                        'allow' => true,
                        'roles' => ['order.process'],
                    ],
                    [
                        'actions' => ['receipt'],
                        'allow' => true,
                        'roles' => ['order.receipt.print'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'cancel' => ['post'],
                    'process' => ['post'],
                    'create-product' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';

        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(
            $this->request->queryParams,
            Yii::$app->user->id,
            $this->canViewAllOrders(),
        );

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'search' => $searchModel->q,
            'status' => $searchModel->statusFilter,
            'filters' => $searchModel->filters,
            'canViewAudit' => $this->canViewAllOrders(),
        ]);
    }

    public function actionView(int $id): string
    {
        $this->layout = 'admin';
        $order = $this->findAccessibleOrder($id);
        Notification::updateAll(
            ['read_at' => time()],
            ['order_id' => $order->id, 'user_id' => Yii::$app->user->id, 'read_at' => null],
        );

        return $this->render('view', [
            'model' => $order,
        ]);
    }

    public function actionReceipt(int $id): string
    {
        if (!Yii::$app->user->can('order.view')) {
            throw new ForbiddenHttpException('You are not allowed to view this order.');
        }

        $this->layout = 'admin';
        $order = $this->findAccessibleOrder($id);

        return $this->render('receipt', [
            'model' => $order,
        ]);
    }

    public function actionConfirmation(int $id): string
    {
        $this->layout = 'admin';

        return $this->render('confirmation', [
            'model' => $this->findAccessibleOrder($id),
        ]);
    }

    public function actionCreate(): Response|string
    {
        if (!$this->canViewAllOrders()) {
            return $this->redirect(['/cart/index']);
        }

        $this->layout = 'admin';

        if (!$this->request->isPost) {
            return $this->render('create', [
                'products' => Product::find()
                    ->where(['status' => Product::STATUS_ACTIVE])
                    ->orderBy(['name' => SORT_ASC])
                    ->all(),
            ]);
        }

        $items = $this->request->post('items', []);

        if (!is_array($items)) {
            throw new BadRequestHttpException('Invalid order items.');
        }

        $order = (new OrderService())->create(
            (int) Yii::$app->user->id,
            $items,
        );

        return $this->redirect($this->getOrderReturnRoute((int) $order->id));
    }

    public function actionCreateProduct(int $id): Response
    {
        $quantity = filter_var(
            $this->request->post('quantity'),
            FILTER_VALIDATE_INT,
        );

        if ($quantity === false || $quantity < 1) {
            throw new BadRequestHttpException('Enter a quantity of at least one.');
        }

        (new ShoppingCart())->add($id, $quantity);
        Yii::$app->session->setFlash('success', 'Product added to your cart.');

        return $this->redirect(['/cart/index']);
    }

    public function actionUpdate(int $id): Response|string
    {
        if (!Yii::$app->user->can('order.view')) {
            throw new ForbiddenHttpException('You are not allowed to view this order.');
        }

        $this->layout = 'admin';
        $order = $this->findAccessibleOrder($id);

        if (!$order->isEditableByCustomer()) {
            throw new ForbiddenHttpException(
                'Only pending orders can be edited.',
            );
        }

        if (!$this->request->isPost) {
            return $this->render('update', [
                'model' => $order,
                'products' => Product::find()
                    ->where(['status' => Product::STATUS_ACTIVE])
                    ->orderBy(['name' => SORT_ASC])
                    ->all(),
            ]);
        }

        $items = $this->request->post('items', []);

        if (!is_array($items)) {
            throw new BadRequestHttpException('Invalid order items.');
        }

        (new OrderService())->update($order, $items);

        Yii::$app->session->setFlash('success', 'Order updated.');

        return $this->redirect($this->getOrderReturnRoute((int) $order->id));
    }

    public function actionCancel(int $id): Response
    {
        $order = $this->findAccessibleOrder($id);

        (new OrderStatusService())->cancel(
            $order,
            (int) Yii::$app->user->id,
            Yii::$app->user->can('order.process'),
        );

        Yii::$app->session->setFlash('success', 'Order cancelled.');

        return $this->redirect($this->getOrderReturnRoute((int) $order->id));
    }

    public function actionProcess(int $id): Response
    {
        $order = $this->findAccessibleOrder($id);
        (new OrderStatusService())->process(
            $order,
            (int) Yii::$app->user->id,
        );

        Yii::$app->session->setFlash('success', 'Order status updated.');

        return $this->redirect($this->getOrderReturnRoute((int) $order->id));
    }

    private function findAccessibleOrder(int $id): Order
    {
        $query = OrderAccessPolicy::scopeToOwner(
            Order::find()->where(['id' => $id]),
            Yii::$app->user->id,
            $this->canViewAllOrders(),
        );

        $order = $query->with([
            'items.product',
            'items.createdBy',
            'items.updatedBy',
            'user',
            'statusHistory.changedBy',
        ])->one();

        if ($order === null) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $order;
    }

    private function canViewAllOrders(): bool
    {
        return OrderAccessPolicy::canViewAllOrders(
            Yii::$app->user->can('admin.access'),
            Yii::$app->user->can('staff.access'),
        );
    }

    /**
     * @return array|string
     */
    private function getOrderReturnRoute(int $id): array|string
    {
        if (Yii::$app->user->can('order.view')) {
            return ['view', 'id' => $id];
        }
        if (Yii::$app->user->can('order.index')) {
            return ['index'];
        }

        return ['/site/dashboard'];
    }
}
