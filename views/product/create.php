<?php

use app\services\Navigation;
use yii\helpers\Html;

/** @var app\models\Product $model */
/** @var array<int, string> $categories */
/** @var array<int, string> $inventoryItems */
/** @var list<array{inventory_id: mixed, quantity_required: mixed}> $ingredientRows */

$this->title = 'Create product';
?>

<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-end">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Product catalog</p>
        <h1 class="mb-1 h3"><?= Html::encode($this->title) ?></h1>
        <p class="mb-0 text-body-secondary">Add a menu item with its price, photo, and availability.</p>
    </div>
    <?= Html::a(
        Yii::$app->user->can('product.index') ? 'Back to menu' : 'Back to dashboard',
        Yii::$app->user->can('product.index') ? Navigation::url(Navigation::PRODUCT_INDEX) : Navigation::url(Navigation::DASHBOARD),
        ['class' => 'btn btn-outline-secondary'],
    ) ?>
</div>

<?= $this->render('_form', [
    'model' => $model,
    'categories' => $categories,
    'inventoryItems' => $inventoryItems,
    'ingredientRows' => $ingredientRows,
]) ?>