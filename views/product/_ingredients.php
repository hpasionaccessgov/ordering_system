<?php

use app\models\Product;
use yii\helpers\Html;

/** @var Product $model */
/** @var array<int, string> $inventoryItems */
/** @var list<array{inventory_id: mixed, quantity_required: mixed}> $ingredientRows */

$rows = $ingredientRows === []
    ? [['inventory_id' => '', 'quantity_required' => 1]]
    : $ingredientRows;
$selectOptions = [
    'class' => 'form-select',
    'prompt' => 'Select an inventory item',
    'required' => true,
];
$quantityOptions = [
    'class' => 'form-control',
    'min' => 1,
    'step' => 1,
    'required' => true,
    'inputmode' => 'numeric',
];
$this->registerJs(<<<'JS'
(() => {
    const tbody = document.querySelector('[data-ingredient-rows]');
    const template = document.getElementById('ingredient-row-template');
    const addButton = document.querySelector('[data-add-ingredient]');
    if (!tbody || !template || !addButton) {
        return;
    }

    let index = Number(tbody.dataset.nextIndex || '0');
    addButton.addEventListener('click', () => {
        tbody.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(index++)));
    });
    tbody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-ingredient]');
        if (button) {
            button.closest('tr')?.remove();
        }
    });
})();
JS);
?>
<section class="card admin-panel mt-4">
    <div class="card-header">
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Recipe</p>
        <h2 class="h5 mb-0">Ingredients</h2>
    </div>
    <div class="card-body p-4">
        <p class="text-body-secondary small">
            Quantities are required per single product. They are deducted from inventory when an order is completed.
        </p>

        <?php if ($inventoryItems === []) : ?>
            <div class="alert alert-warning" role="alert">
                No active inventory items are available. Add them in Inventory first.
            </div>
        <?php endif; ?>

        <?= Html::error($model, 'ingredients', ['class' => 'invalid-feedback d-block mb-3']) ?>

        <div class="table-responsive cafebara-table-scroll">
            <table class="table table-striped table-hover table-bordered align-middle cafebara-data-table">
                <thead>
                    <tr>
                        <th scope="col">Inventory item</th>
                        <th scope="col" style="width: 12rem">Quantity required</th>
                        <th scope="col" style="width: 4rem"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody data-ingredient-rows data-next-index="<?= count($rows) ?>">
                    <?php foreach ($rows as $index => $row) : ?>
                        <tr data-ingredient-row>
                            <td>
                                <?= Html::dropDownList(
                                    "ingredients[$index][inventory_id]",
                                    $row['inventory_id'] ?? '',
                                    $inventoryItems,
                                    $selectOptions,
                                ) ?>
                            </td>
                            <td>
                                <?= Html::input(
                                    'number',
                                    "ingredients[$index][quantity_required]",
                                    $row['quantity_required'] ?? 1,
                                    $quantityOptions,
                                ) ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-remove-ingredient aria-label="Remove ingredient">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <template id="ingredient-row-template">
            <tr data-ingredient-row>
                <td>
                    <?= Html::dropDownList(
                        'ingredients[__INDEX__][inventory_id]',
                        '',
                        $inventoryItems,
                        $selectOptions,
                    ) ?>
                </td>
                <td>
                    <?= Html::input('number', 'ingredients[__INDEX__][quantity_required]', 1, $quantityOptions) ?>
                </td>
                <td>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-remove-ingredient aria-label="Remove ingredient">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                </td>
            </tr>
        </template>

        <button type="button" class="btn btn-outline-primary mt-3" data-add-ingredient <?= $inventoryItems === [] ? 'disabled' : '' ?>>
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add ingredient
        </button>
    </div>
</section>
