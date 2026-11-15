<?php

use app\models\Product;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Products';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->can('product.create')): ?>
        <?= Html::a(
            'Create product',
            ['/product/create'],
            ['class' => 'btn btn-primary'],
        ) ?>
    <?php endif; ?>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'name',
        'description',
        'price:currency',
        [
            'attribute' => 'status',
            'value' => static fn (Product $model): string => $model->isActive()
                ? 'Active'
                : 'Archived',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view} {update}',
        ],
    ],
]) ?>