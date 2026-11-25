<?php

use app\models\Reward;
use yii\helpers\Html;

/** @var app\models\RewardSearch $model */
?>
<?= Html::beginForm(['/admin/rewards'], 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', $model->q, [
            'class' => 'form-control',
            'placeholder' => 'Search rewards by name or description',
            'aria-label' => 'Search rewards',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::dropDownList('RewardSearch[status]', $model->status, [
            '' => 'All statuses',
            Reward::STATUS_ACTIVE => 'Active',
            Reward::STATUS_INACTIVE => 'Disabled',
        ], ['class' => 'form-select', 'aria-label' => 'Reward status']) ?>
    </div>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', ['/admin/rewards'], ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= Html::errorSummary($model, ['class' => 'alert alert-danger mt-2']) ?>
<?= Html::endForm() ?>
