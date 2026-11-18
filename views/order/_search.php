<?php

use yii\helpers\Html;

/** @var string $search */

$sort = Yii::$app->request->get('sort');
?>
<?= Html::beginForm(['/order/index'], 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', $search, [
            'class' => 'form-control',
            'placeholder' => 'Search by order ID or status',
            'aria-label' => 'Search orders',
        ]) ?>
    </div>
    <?php if (is_string($sort) && $sort !== '') : ?>
        <?= Html::hiddenInput('sort', $sort) ?>
    <?php endif; ?>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', ['/order/index'], ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= Html::endForm() ?>
