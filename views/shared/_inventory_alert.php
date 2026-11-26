<?php

use app\services\Navigation;
use app\models\Inventory;
use app\services\InventoryService;
use yii\helpers\Html;

/** @var Inventory[] $lowStockItems */

if ($lowStockItems === []) {
    return;
}
?>
<div class="alert alert-warning d-flex align-items-start gap-2 mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
    <div>
        <strong>Low stock alert</strong>
        <ul class="mb-1">
            <?php foreach ($lowStockItems as $item) : ?>
                <li><?= Html::encode(InventoryService::lowStockMessage($item)) ?></li>
            <?php endforeach; ?>
        </ul>
        <?= Html::a('Review inventory', Navigation::url(Navigation::INVENTORY_INDEX), ['class' => 'alert-link']) ?>
    </div>
</div>
