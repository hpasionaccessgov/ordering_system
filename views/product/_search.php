<?php

use yii\helpers\Html;

/** @var array<int, string> $categories */
/** @var bool $canManageProducts */
/** @var string $status */
/** @var string $search */
/** @var \app\models\ListingFilters $filters */

?>
<?= Html::beginForm(['/product/index'], 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md-3">
        <?= Html::input('search', 'q', $search, [
            'class' => 'form-control',
            'placeholder' => 'Search products',
            'aria-label' => 'Search products',
        ]) ?>
    </div>
    <div class="col-12 col-md-2">
        <?= Html::dropDownList('category_id', Yii::$app->request->get('category_id'), [
            '' => 'All categories',
        ] + $categories, ['class' => 'form-select', 'aria-label' => 'Product category']) ?>
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
    <div class="col-auto"><?= Html::a('Clear', ['/product/index'], ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= $this->render('../shared/_listing_filters', [
    'filters' => $filters,
    'showAudit' => $canManageProducts,
    'showDates' => $canManageProducts,
    'showAmount' => true,
    'amountLabel' => '',
]) ?>
<?= Html::endForm() ?>