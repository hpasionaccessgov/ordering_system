<?php

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string[] $categories */
/** @var bool $canManageProducts */
/** @var bool $canViewProducts */
/** @var string $status */

$this->title = 'Product';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-0">MENU AVAILABLE</p>
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
    </div>
    <?php if (Yii::$app->user->can('product.create')) : ?>
        <?= Html::a(
            'Create product',
            ['/product/create'],
            ['class' => 'btn btn-primary'],
        ) ?>
    <?php endif; ?>
</div>

<?php if ($canViewProducts) : ?>
<?= Html::beginForm(['/product/index'], 'get', ['class' => 'catalog-filter mb-4']) ?>
<div class="row g-2">
    <div class="col-12 col-md-3">
        <?= Html::input('search', 'q', Yii::$app->request->get('q'), [
            'class' => 'form-control',
            'placeholder' => 'Search products',
            'aria-label' => 'Search products',
        ]) ?>
    </div>
    <div class="col-12 col-md-2">
        <?= Html::dropDownList('category', Yii::$app->request->get('category'), [
            '' => 'All categories',
        ] + array_combine($categories, $categories), ['class' => 'form-select']) ?>
    </div>
    <?php if ($canManageProducts) : ?>
        <div class="col-12 col-md-2">
            <?= Html::dropDownList('status', $status, [
                'all' => 'All statuses',
                'active' => 'Active',
                'archived' => 'Archived',
            ], ['class' => 'form-select', 'aria-label' => 'Product status']) ?>
        </div>
    <?php endif; ?>
    <div class="col-6 col-md-1">
        <?= Html::input('number', 'min_price', Yii::$app->request->get('min_price'), [
            'class' => 'form-control',
            'min' => 0,
            'step' => '0.01',
            'placeholder' => 'Min ₱',
        ]) ?>
    </div>
    <div class="col-6 col-md-1">
        <?= Html::input('number', 'max_price', Yii::$app->request->get('max_price'), [
            'class' => 'form-control',
            'min' => 0,
            'step' => '0.01',
            'placeholder' => 'Max ₱',
        ]) ?>
    </div>
    <div class="col-12 col-md-2">
        <?= Html::dropDownList('sort', Yii::$app->request->get('sort', 'name'), [
            'name' => 'Name: A to Z',
            '-name' => 'Name: Z to A',
            'price' => 'Price: low to high',
            '-price' => 'Price: high to low',
        ], ['class' => 'form-select', 'aria-label' => 'Sort products']) ?>
    </div>
    <div class="col-12 col-md-1">
        <?= Html::submitButton('Filter', ['class' => 'btn btn-primary w-100']) ?>
    </div>
</div>
<?= Html::endForm() ?>
<?php else : ?>
    <div class="alert alert-warning mb-4" role="alert">
        You do not have permission to view products.
    </div>
<?php endif; ?>

<?php if ($canViewProducts) : ?>
<div class="row g-3">
    <?php foreach ($dataProvider->getModels() as $product) : ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <?= $this->render('../shared/_product_card', ['product' => $product]) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php if ($dataProvider->getCount() === 0) : ?>
    <div class="card admin-panel">
        <div class="card-body text-center py-5">
            <h2 class="h5">No products match your filters</h2>
            <p class="text-body-secondary mb-0">Try a different search or category.</p>
        </div>
    </div>
<?php endif; ?>
<?= LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
<?php endif; ?>