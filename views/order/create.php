<?php

use app\models\Product;
use yii\helpers\Html;

/** @var Product[] $products */

$this->title = 'Create order';
?>

<h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>

<?= Html::beginForm(['/order/create'], 'post') ?>

<div class="row g-3">
    <?php foreach ($products as $product): ?>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5"><?= Html::encode($product->name) ?></h2>

                    <p class="text-body-secondary">
                        <?= Html::encode($product->description ?? '') ?>
                    </p>

                    <p class="fw-semibold">
                        <?= Yii::$app->formatter->asCurrency($product->price) ?>
                    </p>

                    <?= Html::hiddenInput(
                        "items[{$product->id}][product_id]",
                        (string) $product->id,
                    ) ?>

                    <?= Html::label(
                        'Quantity',
                        "quantity-{$product->id}",
                        ['class' => 'form-label'],
                    ) ?>

                    <?= Html::input(
                        'number',
                        "items[{$product->id}][quantity]",
                        0,
                        [
                            'id' => "quantity-{$product->id}",
                            'class' => 'form-control',
                            'min' => 0,
                        ],
                    ) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="mt-4">
    <?= Html::submitButton(
        'Place order',
        ['class' => 'btn btn-primary'],
    ) ?>
</div>

<?= Html::endForm() ?>