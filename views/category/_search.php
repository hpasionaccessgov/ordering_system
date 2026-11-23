<?php

use yii\helpers\Html;

/** @var app\models\CategorySearch $model */

$sort = Yii::$app->request->get('sort');
?>
<?= Html::beginForm(['/category/index'], 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', is_string($model->q) ? $model->q : '', [
            'class' => 'form-control',
            'placeholder' => 'Search categories by name, description, or ID',
            'aria-label' => 'Search categories',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('CategorySearch[status]', $model->status, [
            '' => 'All statuses',
            \app\models\Category::STATUS_ACTIVE => 'Active',
            \app\models\Category::STATUS_INACTIVE => 'Inactive',
        ], ['class' => 'form-select', 'aria-label' => 'Category status']) ?>
    </div>
    <?php if (is_string($sort) && $sort !== '') : ?>
        <?= Html::hiddenInput('sort', $sort) ?>
    <?php endif; ?>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', ['/category/index'], ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= Html::errorSummary($model, ['class' => 'alert alert-danger mt-2']) ?>
<?= $this->render('../shared/_listing_filters', [
    'filters' => $model->filters, 'showAudit' => true, 'showDates' => true,
    'showAmount' => false, 'amountLabel' => '',
]) ?>
<?= Html::endForm() ?>
