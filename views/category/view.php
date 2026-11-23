<?php

use app\models\Category;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var Category $model */

$this->title = $model->name;
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Product catalog</p>
        <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="text-body-secondary mb-0">Category details</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (Yii::$app->user->can('category.update')) : ?>
            <?= Html::a('Edit category', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
        <?php if (Yii::$app->user->can('category.index')) : ?>
            <?= Html::a('Back to categories', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        <?php endif; ?>
    </div>
</div>

<section class="card admin-panel">
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'name',
                ['attribute' => 'description', 'value' => $model->description ?? '', 'format' => 'ntext'],
                [
                    'attribute' => 'status',
                    'value' => (int) $model->status === Category::STATUS_ACTIVE ? 'Active' : 'Inactive',
                ],
                ['attribute' => 'created_at', 'format' => 'datetime'],
                ['attribute' => 'updated_at', 'format' => 'datetime'],
                ['label' => 'Created by', 'value' => $model->createdBy->username ?? '—'],
                ['label' => 'Updated by', 'value' => $model->updatedBy->username ?? '—'],
            ],
        ]) ?>
        <?php if (Yii::$app->user->can('category.delete')) : ?>
            <?= Html::beginForm(['delete', 'id' => $model->id], 'post', ['class' => 'mt-3']) ?>
            <?= Html::submitButton('Delete category', [
                'class' => 'btn btn-outline-danger',
                'data-confirm-modal' => 'Delete this category? Categories assigned to products cannot be deleted.',
            ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
</section>