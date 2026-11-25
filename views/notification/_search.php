<?php

declare(strict_types=1);

use app\models\Order;
use yii\helpers\Html;

/** @var string $search */
/** @var string $status */
/** @var string $read */
?>
<?= Html::beginForm(['/notifications'], 'get', [
    'class' => 'catalog-filter p-3 mb-4',
    'role' => 'search',
]) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', $search, [
            'class' => 'form-control',
            'placeholder' => 'Search status or order number',
            'aria-label' => 'Search notifications',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('status', $status, ['' => 'All statuses'] + array_combine(
            Order::statusValues(),
            array_map('ucfirst', Order::statusValues()),
        ), [
            'class' => 'form-select',
            'aria-label' => 'Filter notifications by status',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('read', $read, [
            '' => 'All notifications',
            'unread' => 'Unread',
            'read' => 'Read',
        ], [
            'class' => 'form-select',
            'aria-label' => 'Filter notifications by read state',
        ]) ?>
    </div>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', ['/notifications'], ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= Html::endForm() ?>
