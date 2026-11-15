<?php

use app\models\Product;
use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string[] $categories */

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

<?= Html::beginForm(['/product/index'], 'get', ['class' => 'catalog-filter mb-4']) ?>
<div class="row g-2">
    <div class="col-12 col-md-4">
        <?= Html::input('search', 'q', Yii::$app->request->get('q'), [
            'class' => 'form-control',
            'placeholder' => 'Search products',
            'aria-label' => 'Search products',
        ]) ?>
    </div>
    <div class="col-12 col-md-3">
        <?= Html::dropDownList('category', Yii::$app->request->get('category'), [
            '' => 'All categories',
        ] + array_combine($categories, $categories), ['class' => 'form-select']) ?>
    </div>
    <div class="col-6 col-md-2">
        <?= Html::input('number', 'min_price', Yii::$app->request->get('min_price'), [
            'class' => 'form-control',
            'min' => 0,
            'step' => '0.01',
            'placeholder' => 'Min ₱',
        ]) ?>
    </div>
    <div class="col-6 col-md-2">
        <?= Html::input('number', 'max_price', Yii::$app->request->get('max_price'), [
            'class' => 'form-control',
            'min' => 0,
            'step' => '0.01',
            'placeholder' => 'Max ₱',
        ]) ?>
    </div>
    <div class="col-12 col-md-1">
        <?= Html::submitButton('Filter', ['class' => 'btn btn-primary w-100']) ?>
    </div>
</div>
<?= Html::endForm() ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label' => 'Image',
            'format' => 'raw',
            'value' => static function (Product $model): string {
                if ($model->getImageUrl() === null) {
                    return '<span class="text-body-secondary">No image</span>';
                }

                return Html::img($model->getImageUrl(), [
                    'class' => 'product-table-image',
                    'alt' => $model->name,
                ]);
            },
        ],
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
