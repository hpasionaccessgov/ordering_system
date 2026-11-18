<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Category $model */

$this->title = 'Update category';
$this->params['breadcrumbs'][] = ['label' => 'Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Product catalog</p>
        <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="text-body-secondary mb-0">Update <?= Html::encode($model->name) ?> and its availability.</p>
    </div>
    <?= Html::a(
        Yii::$app->user->can('category.index') ? 'Back to categories' : 'Back to dashboard',
        Yii::$app->user->can('category.index') ? ['index'] : ['/site/dashboard'],
        ['class' => 'btn btn-outline-secondary'],
    ) ?>
</div>

<?= $this->render('_form', ['model' => $model]) ?>
