<?php

use app\models\Inventory;
use app\widgets\AdminGridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\InventorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Inventory';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Stock control</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
    <?php if (Yii::$app->user->can('inventory.create')) : ?>
        <?= Html::a('Create inventory item', ['create'], ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<?= $this->render('_search', ['model' => $searchModel]) ?>

<section class="card admin-panel">
    <div class="card-header">
        <p class="mb-0 text-body-secondary">Review stock levels and minimum thresholds.</p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'rowOptions' => static fn(Inventory $inventory): array => $inventory->isLowStock()
            ? ['class' => 'table-danger']
            : [],
        'columns' => [
            'id',
            'name',
            'unit',
            [
                'attribute' => 'current_stock',
                'format' => 'raw',
                'value' => static function (Inventory $inventory): string {
                    $value = Html::encode((string) $inventory->current_stock);
                    if ($inventory->isOutOfStock()) {
                        return $value . ' <span class="badge text-bg-danger">Out of stock</span>';
                    }
                    if ($inventory->isLowStock()) {
                        return $value . ' <span class="badge text-bg-warning">Low stock</span>';
                    }

                    return $value;
                },
            ],
            'minimum_stock',
            [
                'attribute' => 'status',
                'value' => static fn(Inventory $inventory): string => $inventory->getStatusLabel(),
            ],
            'created_at:datetime',
            'updated_at:datetime',
            [
                'label' => 'Actions',
                'format' => 'raw',
                'value' => static function (Inventory $inventory): string {
                    $actions = [];
                    if (Yii::$app->user->can('inventory.view')) {
                        $actions[] = Html::a(
                            'View',
                            ['view', 'id' => $inventory->id],
                            ['class' => 'btn btn-sm btn-secondary', 'aria-label' => 'View ' . $inventory->name],
                        );
                    }
                    if (Yii::$app->user->can('inventory.update') && Yii::$app->user->can('inventory.view')) {
                        $actions[] = Html::a(
                            'Edit',
                            ['update', 'id' => $inventory->id],
                            ['class' => 'btn btn-sm btn-primary', 'aria-label' => 'Edit ' . $inventory->name],
                        );
                    }
                    if (Yii::$app->user->can('inventory.delete')) {
                        $actions[] = Html::beginForm(['delete', 'id' => $inventory->id], 'POST', ['class' => 'd-inline'])
                            . Html::submitButton('Delete', [
                                'class' => 'btn btn-sm btn-danger',
                                'aria-label' => 'Delete ' . $inventory->name,
                                'data-confirm-modal' => 'Delete this item? Items with stock history cannot be deleted.',
                            ])
                            . Html::endForm();
                    }

                    return implode(' ', $actions);
                },
            ],
        ],
    ]) ?>
</section>