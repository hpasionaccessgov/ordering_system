<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Order;
use app\services\OrderService;
use Yii;
use yii\data\ActiveDataProvider;
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
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['order.view'],
                    ],
                    [
                        'actions' => ['create'],
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
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'cancel' => ['post'],
                    'process' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';

        $query = Order::find()->orderBy(['created_at' => SORT_DESC]);

        if (!Yii::$app->user->can('order.process')) {
            $query->andWhere(['user_id' => Yii::$app->user->id]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
            ]),
        ]);
    }

    public function actionView(int $id): string
    {
        $this->layout = 'admin';
        $order = $this->findAccessibleOrder($id);

        return $this->render('view', [
            'model' => $order,
        ]);
    }

    public function actionCreate(): Response|string
    {
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

        return $this->redirect(['view', 'id' => $order->id]);
    }

    public function actionUpdate(int $id): Response|string
    {
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

        return $this->redirect(['view', 'id' => $order->id]);
    }

    public function actionCancel(int $id): Response
    {
        $order = $this->findAccessibleOrder($id);

        $isStaff = Yii::$app->user->can('order.process');

        if (
            $isStaff
            && !$order->canBeCancelledByStaff()
        ) {
            throw new ForbiddenHttpException(
                'This order cannot be cancelled at its current status.',
            );
        }

        if (
            !$isStaff
            && !$order->canBeCancelledByCustomer()
        ) {
            throw new ForbiddenHttpException(
                'Customers can only cancel pending orders.',
            );
        }

        $order->status = Order::STATUS_CANCELLED;

        if (!$order->save(false, ['status', 'updated_at'])) {
            throw new ForbiddenHttpException('Order could not be cancelled.');
        }

        Yii::$app->session->setFlash('success', 'Order cancelled.');

        return $this->redirect(['view', 'id' => $order->id]);
    }

    public function actionProcess(int $id): Response
    {
        $order = $this->findAccessibleOrder($id);

        $allowedStatuses = [
            Order::STATUS_CONFIRMED => Order::STATUS_PROCESSING,
            Order::STATUS_PROCESSING => Order::STATUS_COMPLETED,
        ];

        if (!isset($allowedStatuses[$order->status])) {
            throw new ForbiddenHttpException(
                'This order cannot be processed from its current status.',
            );
        }

        $order->status = $allowedStatuses[$order->status];

        if (!$order->save(false, ['status', 'updated_at'])) {
            throw new ForbiddenHttpException('Order status could not be updated.');
        }

        Yii::$app->session->setFlash('success', 'Order status updated.');

        return $this->redirect(['view', 'id' => $order->id]);
    }

    private function findAccessibleOrder(int $id): Order
    {
        $query = Order::find()->where(['id' => $id]);

        if (!Yii::$app->user->can('order.process')) {
            $query->andWhere(['user_id' => Yii::$app->user->id]);
        }

        $order = $query->with(['items.product'])->one();

        if ($order === null) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $order;
    }
}
