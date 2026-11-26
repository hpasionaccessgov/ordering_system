<?php

use app\services\Navigation;
use yii\helpers\Html;

/** @var string $search */
/** @var string $status */
/** @var array $filters */
/** @var bool $canViewAudit */

$sort = Yii::$app->request->get('sort');
?>
<?= Html::beginForm(Navigation::url(Navigation::ORDER_INDEX), 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', $search, [
            'class' => 'form-control',
            'placeholder' => 'Search order number, status, or customer',
            'aria-label' => 'Search orders',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('status', $status, ['' => 'All statuses'] + array_combine(
            \app\models\Order::statusValues(),
            array_map([\app\models\Order::class, 'statusLabel'], \app\models\Order::statusValues()),
        ), [
            'class' => 'form-select',
            'aria-label' => 'Filter orders by status',
        ]) ?>
    </div>
    <?php if (is_string($sort) && $sort !== '') : ?>
        <?= Html::hiddenInput('sort', $sort) ?>
    <?php endif; ?>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', Navigation::url(Navigation::ORDER_INDEX), ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= $this->render('../shared/_listing_filters', [
    'filters' => $filters,
    'showAudit' => $canViewAudit,
    'showDates' => true,
    'showAmount' => true,
    'amountLabel' => '',
]) ?>
<?= Html::endForm() ?>