<?php

use app\models\Product;
use yii\helpers\Html;

/** @var Product $model */

$this->title = $model->name;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?= Html::encode($model->name) ?></h1>

    <?= Html::a(
        'Back to products',
        ['/product/index'],
        ['class' => 'btn btn-outline-secondary'],
    ) ?>
</div>

<p><?= nl2br(Html::encode($model->description ?? '')) ?></p>

<p class="fw-bold">
    <?= Yii::$app->formatter->asCurrency($model->price) ?>
</p>

<?php if (Yii::$app->user->can('product.update')): ?>
    <?= Html::a(
        'Update',
        ['/product/update', 'id' => $model->id],
        ['class' => 'btn btn-primary'],
    ) ?>
<?php endif; ?>

<?php if (
    Yii::$app->user->can('product.archive')
    && $model->isActive()
): ?>
    <?= Html::beginForm(
        ['/product/archive', 'id' => $model->id],
        'post',
        ['class' => 'd-inline'],
    ) ?>

    <?= Html::submitButton(
        'Archive product',
        [
            'class' => 'btn btn-outline-danger',
            'data-confirm' => 'Archive this product?',
        ],
    ) ?>

    <?= Html::endForm() ?>
<?php endif; ?>