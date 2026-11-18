<?php

use app\models\Order;
use app\widgets\AdminGridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $search */

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;

$columns = [
    'id',
    'status',
    'total_amount:currency',
    [
        'attribute' => 'created_at',
        'format' => ['date', 'php:Y-m-d H:i'],
    ],
];

if (Yii::$app->user->can('order.view')) {
    $columns[] = [
        'class' => 'yii\grid\ActionColumn',
        'header' => 'Action',
        'template' => '{view}',
        'buttons' => [
            'view' => static fn($url, Order $model): string => Html::a(
                'View',
                ['/order/view', 'id' => $model->id],
                ['class' => 'btn btn-sm btn-outline-primary'],
            ),
        ],
    ];
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">CUSTOMER ORDERS</p>
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
    </div>
    <?= Html::a('Create order', ['/order/create'], ['class' => 'btn btn-primary']) ?>
</div>

<?= $this->render('_search', ['search' => $search]) ?>

<div class="order-table-card">
    <div class="card-header">
        <p class="text-body-secondary mb-0">Review order details.</p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => $columns,
    ]) ?>

</div>