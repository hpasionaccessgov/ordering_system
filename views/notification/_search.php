<?php

declare(strict_types=1);

use app\services\Navigation;

use app\models\Notification;
use app\models\Order;
use yii\helpers\Html;

/** @var string $search */
/** @var string $status */
/** @var string $read */
/** @var string $category */
/** @var string $sort */
?>
<?= Html::beginForm(Navigation::url(Navigation::NOTIFICATIONS), 'get', [
    'class' => 'catalog-filter p-3 mb-4',
    'role' => 'search',
]) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', $search, [
            'class' => 'form-control',
            'placeholder' => 'Search message, type, or order number',
            'aria-label' => 'Search notifications',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('category', $category, ['' => 'All categories'] + Notification::categoryLabels(), [
            'class' => 'form-select',
            'aria-label' => 'Filter notifications by category',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('status', $status, ['' => 'All statuses'] + array_combine(
            Order::statusValues(),
            array_map(static fn(string $value): string => Order::statusLabel($value), Order::statusValues()),
        ), [
            'class' => 'form-select',
            'aria-label' => 'Filter notifications by status',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('read', $read, [
            '' => 'All read states',
            'unread' => 'Unread',
            'read' => 'Read',
        ], [
            'class' => 'form-select',
            'aria-label' => 'Filter notifications by read state',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('sort', $sort, [
            'latest' => 'Latest first',
            'oldest' => 'Oldest first',
            'unread' => 'Unread first',
        ], [
            'class' => 'form-select',
            'aria-label' => 'Sort notifications',
        ]) ?>
    </div>
    <div class="col-auto"><?= Html::submitButton('Apply', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', Navigation::url(Navigation::NOTIFICATIONS), ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= Html::endForm() ?>
