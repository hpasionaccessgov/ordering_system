<?php

use app\models\Inventory;
use app\widgets\AdminGridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var Inventory $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Inventory', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Stock control</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php if (Yii::$app->user->can('inventory.update') && Yii::$app->user->can('inventory.view')) : ?>
            <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
        <?php if (Yii::$app->user->can('inventory.delete')) : ?>
            <?= Html::beginForm(['delete', 'id' => $model->id], 'POST', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('Delete', [
                'class' => 'btn btn-outline-danger',
                'data-confirm-modal' => 'Delete this item? Items with stock history cannot be deleted.',
            ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
</div>

<?php if ($model->isLowStock()) : ?>
    <?= $this->render('../shared/_inventory_alert', ['lowStockItems' => [$model]]) ?>
<?php endif; ?>

<section class="card admin-panel mb-4">
    <div class="card-header">
        <p class="mb-0 text-body-secondary">Current stock details.</p>
    </div>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'description:ntext',
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
        ],
    ]) ?>
</section>

<section class="card admin-panel">
    <div class="card-header">
        <p class="mb-0 text-body-secondary">Stock movements for this item.</p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $model->transactions,
            'pagination' => false,
            'sort' => false,
        ]),
        'emptyText' => 'No stock movements recorded yet.',
        'columns' => [
            'created_at:datetime',
            [
                'attribute' => 'transaction_type',
                'value' => static fn(\app\models\InventoryTransaction $transaction): string => $transaction->getTypeLabel(),
            ],
            'quantity',
            'remarks:ntext',
            [
                'attribute' => 'created_by',
                'label' => 'Created by',
                'value' => static fn(\app\models\InventoryTransaction $transaction): string => $transaction->createdBy?->username ?? '—',
            ],
        ],
    ]) ?>
</section>
