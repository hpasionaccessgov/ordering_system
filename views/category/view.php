<?php

use app\models\Category;
use yii\helpers\Html;

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
        <dl class="row mb-0">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9"><?= Html::encode($model->id) ?></dd>
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9"><?= Html::encode($model->name) ?></dd>
            <dt class="col-sm-3">Description</dt>
            <dd class="col-sm-9"><?= nl2br(Html::encode($model->description ?? '')) ?></dd>
            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                <?= (int) $model->status === Category::STATUS_ACTIVE ? 'Active' : 'Inactive' ?>
            </dd>
            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9"><?= Yii::$app->formatter->asDatetime($model->created_at) ?></dd>
            <dt class="col-sm-3">Updated</dt>
            <dd class="col-sm-9"><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></dd>
        </dl>
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
