<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Category $model */

$this->title = 'Update category';
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-start">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Product catalog</p>
        <h1 class="mb-1 h3"><?= Html::encode($this->title) ?></h1>
        <p class="mb-0 text-body-secondary">Update <?= Html::encode($model->name) ?> and its availability.</p>
    </div>
    <?= Html::a(
        Yii::$app->user->can('category.index') ? 'Back to categories' : 'Back to dashboard',
        Yii::$app->user->can('category.index') ? ['index'] : ['/dashboard'],
        ['class' => 'btn btn-secondary'],
    ) ?>
</div>

<?= $this->render('_form', ['model' => $model]) ?>
