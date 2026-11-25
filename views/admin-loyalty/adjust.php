<?php

use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var app\models\AdjustPointsForm $model */
/** @var array<int|string, string> $customerOptions */

$this->title = 'Adjust loyalty points';
$this->params['breadcrumbs'][] = ['label' => 'Loyalty', 'url' => ['/admin/loyalty']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-start">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Loyalty</p>
        <h1 class="mb-1 h3"><?= Html::encode($this->title) ?></h1>
        <p class="mb-0 text-body-secondary">Use a negative number to deduct points. Adjustments never reduce the customer's tier.</p>
    </div>
    <?= Html::a('Back to transactions', ['/admin/loyalty/transactions'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<section class="card admin-panel">
    <div class="card-body">
        <?php if ($customerOptions === []) : ?>
            <div class="alert alert-warning">No customers are available yet.</div>
        <?php endif; ?>
        <?php $form = AdminActiveForm::begin(); ?>
        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>
        <?= $form->field($model, 'userId')->dropDownList(['' => 'Select a customer'] + $customerOptions) ?>
        <?= $form->field($model, 'points')->input('number', ['step' => 1]) ?>
        <?= $form->field($model, 'remarks')->textarea(['rows' => 3]) ?>
        <?= Html::submitButton('Save adjustment', ['class' => 'btn btn-primary']) ?>
        <?php AdminActiveForm::end(); ?>
    </div>
</section>
