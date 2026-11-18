<?php

use yii\helpers\Html;

/** @var app\models\Product $model */
/** @var array<int, string> $categories */

$this->title = 'Update product';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Product catalog</p>
        <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="text-body-secondary mb-0">
            Update <?= Html::encode($model->name) ?> and its customer-facing menu details.
        </p>
    </div>
    <?= Html::a(
        Yii::$app->user->can('product.index') ? 'Back to menu' : 'Back to dashboard',
        Yii::$app->user->can('product.index') ? ['/product/index'] : ['/site/dashboard'],
        ['class' => 'btn btn-outline-secondary'],
    ) ?>
</div>

<?= $this->render('_form', ['model' => $model, 'categories' => $categories]) ?>