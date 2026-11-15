<?php

use app\models\Order;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Orders';
?>

<div class="order-page">
<div class="d-flex justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->can('order.create')): ?>
        <?= Html::a(
            'Create order',
            ['/order/create'],
            ['class' => 'btn btn-primary'],
        ) ?>
    <?php endif; ?>
</div>

<div class="order-table-card">
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-hover align-middle'],
    'summaryOptions' => ['class' => 'px-3 pt-3 mb-0 text-body-secondary small'],
    'options' => ['class' => 'table-responsive'],
    'columns' => [
        'id',
        'status',
        'total_amount:currency',
        [
            'attribute' => 'created_at',
            'format' => ['date', 'php:Y-m-d H:i'],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => static fn ($url, Order $model): string => Html::a(
                    'View',
                    ['/order/view', 'id' => $model->id],
                    ['class' => 'btn btn-sm btn-outline-primary'],
                ),
            ],
        ],
    ],
]) ?>
</div>
</div>
