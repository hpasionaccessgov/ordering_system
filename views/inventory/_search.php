<?php

use app\models\Inventory;
use yii\helpers\Html;

/** @var app\models\InventorySearch $model */

$sort = Yii::$app->request->get('sort');
?>
<?= Html::beginForm(['/inventory/index'], 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', is_string($model->q) ? $model->q : '', [
            'class' => 'form-control',
            'placeholder' => 'Search inventory by name, unit, description, or ID',
            'aria-label' => 'Search inventory',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('InventorySearch[status]', $model->status, ['' => 'All statuses'] + Inventory::statusList(), [
            'class' => 'form-select',
            'aria-label' => 'Inventory status',
        ]) ?>
    </div>
    <?php if (is_string($sort) && $sort !== '') : ?>
        <?= Html::hiddenInput('sort', $sort) ?>
    <?php endif; ?>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', ['/inventory/index'], ['class' => 'btn btn-outline-secondary']) ?></div>
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