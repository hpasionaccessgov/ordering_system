<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Inventory $model */

$this->title = 'Update ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Inventory', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Stock control</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
</div>

<?= $this->render('_form', ['model' => $model]) ?>
