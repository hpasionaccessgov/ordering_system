<?php

use app\models\Reward;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var Reward $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Rewards', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-start">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Loyalty</p>
        <h1 class="mb-1 h3"><?= Html::encode($this->title) ?></h1>
        <p class="mb-0 text-body-secondary">Reward details</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (Yii::$app->user->can('reward.update')) : ?>
            <?= Html::a('Edit reward', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
        <?= Html::a('Back to rewards', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>

<section class="card admin-panel">
    <div class="card-body">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                'name',
                [
                    'attribute' => 'product_id',
                    'value' => static fn(Reward $model): string => $model->product?->name ?? '—',
                ],
                'description:ntext',
                [
                    'attribute' => 'point_cost',
                    'value' => Yii::$app->formatter->asInteger($model->point_cost),
                ],
                [
                    'attribute' => 'status',
                    'value' => $model->getStatusLabel(),
                ],
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</section>
