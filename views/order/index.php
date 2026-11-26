<?php

use app\services\Navigation;
use app\models\Order;
use app\widgets\AdminGridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */
/** @var string $status */
/** @var array $filters */
/** @var bool $canViewAudit */

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;

$columns = [
    'order_number',
    [
        'attribute' => 'status',
        'value' => static fn(Order $order): string => Order::statusLabel((string) $order->status),
    ],
    'total_amount:currency',
    'created_at:datetime',
    'updated_at:datetime',
];

// Customer and audit columns are internal: admins and staff only.
if (Yii::$app->user->can('admin.access') || Yii::$app->user->can('staff.access')) {
    array_splice($columns, 1, 0, [[
        'label' => 'Customer',
        'value' => static fn(Order $order): string =>
        $order->user->full_name ?: $order->user->username,
    ]]);

    $columns[] = [
        'attribute' => 'created_by',
        'label' => 'Created by',
        'value' => static fn(Order $order): string =>
        $order->createdBy?->username ?? '—',
    ];
    $columns[] = [
        'attribute' => 'updated_by',
        'label' => 'Updated by',
        'value' => static fn(Order $order): string =>
        $order->updatedBy?->username ?? '—',
    ];
}

if (Yii::$app->user->can('order.view')) {
    $canOrderAgain = !Yii::$app->user->can('admin.access')
        && !Yii::$app->user->can('staff.access')
        && Yii::$app->user->can('cart.add');
    $columns[] = [
        'class' => 'yii\grid\ActionColumn',
        'header' => 'Action',
        'template' => $canOrderAgain ? '{view} {order-again}' : '{view}',
        'buttons' => [
            'view' => static fn($url, Order $model): string => Html::a(
                'View',
                Navigation::url(Navigation::ORDER_VIEW, ['id' => $model->id]),
                ['class' => 'btn btn-sm btn-outline-secondary'],
            ),
            'order-again' => static fn($url, Order $model): string => $model->status === Order::STATUS_COMPLETED
                ? Html::beginForm(Navigation::url(Navigation::ORDER_AGAIN, ['id' => $model->id]), 'POST', ['class' => 'd-inline'])
                    . Html::submitButton('Order Again', ['class' => 'btn btn-sm btn-primary'])
                    . Html::endForm()
                : '',
        ],
    ];
}
?>

<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">CUSTOMER ORDERS</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
    <?php if (Yii::$app->user->can('cart.index')) : ?>
        <?= Html::a('Open cart', Navigation::url(Navigation::CART_INDEX), ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<?= $this->render('_search', [
    'search' => $search,
    'status' => $status,
    'filters' => $filters,
    'canViewAudit' => $canViewAudit,
]) ?>

<?php if ($dataProvider->getCount() === 0) : ?>
    <div class="card admin-panel cafebara-empty-state">
        <div class="py-5 card-body text-center">
            <span class="cafebara-empty-icon" aria-hidden="true"><i class="bi bi-cup-hot"></i></span>
            <h2 class="mt-3 h5">No orders yet.</h2>
            <p class="mb-3 text-body-secondary">Order your first drink today.</p>
            <?= Html::a('Browse Menu', Navigation::url(Navigation::MENU), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php else : ?>
    <section class="card admin-panel">
        <div class="card-header">
            <p class="mb-0 text-body-secondary">Review order details.</p>
        </div>
        <?= AdminGridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => $columns,
        ]) ?>
    </section>
<?php endif; ?>