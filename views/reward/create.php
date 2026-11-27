<?php

use yii\helpers\Html;

/** @var app\models\Reward $model */
/** @var array<int, string> $products */

$this->title = 'Create reward';
$this->params['breadcrumbs'][] = ['label' => 'Rewards', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-start">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Loyalty</p>
        <h1 class="mb-1 h3"><?= Html::encode($this->title) ?></h1>
        <p class="mb-0 text-body-secondary">Add a new treat customers can redeem with points.</p>
    </div>
</div>

<?= $this->render('_form', ['model' => $model, 'products' => $products]) ?>
