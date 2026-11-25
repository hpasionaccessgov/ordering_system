<?php

use yii\helpers\Html;

/** @var string $search */
/** @var array $roles */
/** @var mixed $model */

$sort = Yii::$app->request->get('sort');
?>
<?= Html::beginForm(['/admin/users'], 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', $search, [
            'class' => 'form-control',
            'placeholder' => 'Search by username, name, or email',
            'aria-label' => 'Search users',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('status', $model->statusFilter, [
            '' => 'All statuses',
            '10' => 'Active',
            '0' => 'Inactive',
            '20' => 'Disabled',
        ], ['class' => 'form-select', 'aria-label' => 'User status']) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('role', $model->roleFilter, ['' => 'All roles'] + \yii\helpers\ArrayHelper::map($roles, 'name', 'name'), [
            'class' => 'form-select',
            'aria-label' => 'User role',
        ]) ?>
    </div>
    <?php if (is_string($sort) && $sort !== '') : ?>
        <?= Html::hiddenInput('sort', $sort) ?>
    <?php endif; ?>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', ['/admin/users'], ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= Html::errorSummary($model, ['class' => 'alert alert-danger mt-2']) ?>
<?= $this->render('../shared/_listing_filters', [
    'filters' => $model->filters,
    'showAudit' => true,
    'showDates' => true,
    'showAmount' => false,
    'amountLabel' => '',
]) ?>
<?= Html::endForm() ?>