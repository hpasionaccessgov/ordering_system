<?php

use app\models\Product;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var Product $model */
/** @var array<int, string> $categories */
/** @var array<int, string> $inventoryItems */
/** @var list<array{inventory_id: mixed, quantity_required: mixed}> $ingredientRows */

$initialImageUrl = $model->getImageUrl();
$cancelRoute = Yii::$app->user->can('product.index')
    ? ['/product/index']
    : ['/site/dashboard'];
$this->registerJs(<<<'JS'
(() => {
    const input = document.getElementById('product-image-upload');
    const preview = document.querySelector('[data-product-image-preview]');
    const placeholder = document.querySelector('[data-product-image-placeholder]');
    if (!input || !preview || !placeholder) {
        return;
    }

    let previewUrl = null;
    input.addEventListener('change', () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        const [file] = input.files || [];
        if (!file) {
            preview.classList.toggle('d-none', preview.dataset.hasImage !== 'true');
            placeholder.classList.toggle('d-none', preview.dataset.hasImage === 'true');
            return;
        }

        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
    });
})();
JS);

$form = AdminActiveForm::begin([
    'options' => [
        'class' => 'product-editor-form',
        'enctype' => 'multipart/form-data',
    ],
]);
?>

<div class="row g-4 align-items-start">
    <div class="col-12 col-xl-8">
        <section class="card h-100 admin-panel product-editor-card">
            <div class="card-header">
                <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Menu listing</p>
                <h2 class="mb-0 h5">Product details</h2>
            </div>
            <div class="p-4 card-body">
                <?= $form->field($model, 'name')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'e.g. Cafébara House Latte',
                ]) ?>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'category_id')->dropDownList($categories, [
                            'prompt' => 'Select a category',
                        ]) ?>
                    </div>
                    <div class="col-12 col-md-6">
                        <?= $form->field($model, 'price')->input('number', [
                            'step' => '0.01',
                            'min' => '0',
                            'placeholder' => '0.00',
                            'inputmode' => 'decimal',
                        ]) ?>
                    </div>
                </div>

                <?= $form->field($model, 'description')->textarea([
                    'rows' => 6,
                    'placeholder' => 'Describe the ingredients, flavor, or serving details.',
                ]) ?>
            </div>
        </section>
    </div>

    <div class="col-12 col-xl-4">
        <section class="card admin-panel product-editor-card">
            <div class="card-header">
                <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Presentation</p>
                <h2 class="mb-0 h5">Photo and availability</h2>
            </div>
            <div class="p-4 card-body">
                <div class="mb-3 product-editor-preview">
                    <?= Html::tag('img', '', array_merge([
                        'class' => 'product-editor-preview-image' . ($initialImageUrl === null ? ' d-none' : ''),
                        'alt' => 'Product image preview',
                        'data-product-image-preview' => 'true',
                        'data-has-image' => $initialImageUrl === null ? 'false' : 'true',
                    ], $initialImageUrl === null ? [] : ['src' => $initialImageUrl])) ?>
                    <div
                        class="d-none' product-editor-preview-placeholder<?= $initialImageUrl === null ? '' : ' ?>"
                        data-product-image-placeholder>
                        <i class="bi bi-image" aria-hidden="true"></i>
                        <span>Product photo preview</span>
                    </div>
                </div>

                <?= $form->field($model, 'imageFile')->fileInput([
                    'id' => 'product-image-upload',
                    'accept' => 'image/png,image/jpeg,image/webp',
                ])->hint('PNG, JPG, or WebP. Maximum size: 3 MB.') ?>

                <?= $form->field($model, 'status')->dropDownList([
                    Product::STATUS_ACTIVE => 'Available in menu',
                    Product::STATUS_ARCHIVED => 'Archived',
                ]) ?>

                <?= $form->field($model, 'is_featured')->checkbox([
                    'label' => 'Feature this product',
                    'labelOptions' => ['class' => 'form-check-label fw-semibold'],
                    'class' => 'form-check-input',
                ]) ?>
            </div>
        </section>
    </div>
</div>

<?= $this->render('_ingredients', [
    'model' => $model,
    'inventoryItems' => $inventoryItems,
    'ingredientRows' => $ingredientRows,
]) ?>

<div class="d-flex flex-wrap gap-2 mt-4 justify-content-end">
    <?= Html::a('Cancel', $cancelRoute, ['class' => 'btn btn-secondary']) ?>
    <?= Html::submitButton('Save product', ['class' => 'btn btn-primary px-4']) ?>
</div>

<?php AdminActiveForm::end(); ?>
