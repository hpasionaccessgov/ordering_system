<?php

use app\models\Reward;
use app\widgets\AdminGridView;
use yii\helpers\Html;

/** @var app\models\RewardSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rewards';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Loyalty</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
    <?php if (Yii::$app->user->can('reward.create')) : ?>
        <?= Html::a('Create reward', ['create'], ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<?= $this->render('_search', ['model' => $searchModel]) ?>

<section class="card admin-panel">
    <div class="card-header">
        <p class="mb-0 text-body-secondary">Create rewards and disable ones you no longer offer. Redemption history is preserved.</p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'description:ntext',
            [
                'attribute' => 'point_cost',
                'label' => 'Points required',
                'value' => static fn(Reward $reward): string => Yii::$app->formatter->asInteger($reward->point_cost),
            ],
            [
                'attribute' => 'status',
                'value' => static fn(Reward $reward): string => $reward->getStatusLabel(),
            ],
            'created_at:datetime',
            'updated_at:datetime',
            [
                'label' => 'Actions',
                'format' => 'raw',
                'value' => static function (Reward $reward): string {
                    $actions = [];
                    if (Yii::$app->user->can('reward.view')) {
                        $actions[] = Html::a('View', ['view', 'id' => $reward->id], [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'aria-label' => 'View reward ' . $reward->name,
                        ]);
                    }
                    if (Yii::$app->user->can('reward.update')) {
                        $actions[] = Html::a('Edit', ['update', 'id' => $reward->id], [
                            'class' => 'btn btn-sm btn-primary',
                            'aria-label' => 'Edit reward ' . $reward->name,
                        ]);
                    }
                    if (Yii::$app->user->can('reward.delete') && $reward->isActive()) {
                        $actions[] = Html::beginForm(['delete', 'id' => $reward->id], 'POST', ['class' => 'd-inline'])
                            . Html::submitButton('Disable', [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'aria-label' => 'Disable reward ' . $reward->name,
                                'data-confirm-modal' => 'Disable ' . $reward->name . '? Customers will no longer see it.',
                            ])
                            . Html::endForm();
                    }

                    return implode(' ', $actions);
                },
            ],
        ],
    ]) ?>
</section>
