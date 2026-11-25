<?php

use app\models\Product;
use yii\helpers\Html;

/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */

$bestSellers = $bestSellers ?? [];
$products = Product::find()
    ->where(['id' => array_column($bestSellers, 'product_id')])
    ->indexBy('id')
    ->all();
?>
<section class="mb-4 card admin-panel best-sellers-panel">
    <div class="card-header bg-transparent">
        <h2 class="mb-0 h5">Best sellers</h2>
    </div>
    <?php if ($bestSellers === []) : ?>
        <div class="card-body text-body-secondary">
            No completed orders yet.
        </div>
    <?php else : ?>
        <ol class="list-group list-group-numbered list-group-flush">
            <?php foreach ($bestSellers as $item) : ?>
                <?php $product = $products[(int) $item['product_id']] ?? null; ?>
                <li class="d-flex flex-wrap gap-3 list-group-item justify-content-between align-items-center">
                    <div class="d-flex gap-3 align-items-center">
                        <?php if ($product?->getImageUrl() !== null) : ?>
                            <?= Html::img($product->getImageUrl(), [
                                'class' => 'best-seller-image',
                                'alt' => '',
                                'loading' => 'lazy',
                            ]) ?>
                        <?php else : ?>
                            <span class="best-seller-image best-seller-placeholder" aria-hidden="true">
                                <i class="bi bi-cup-hot"></i>
                            </span>
                        <?php endif; ?>
                        <?= Html::a(Html::encode($item['product_name']), ['/catalog/products/' . $item['product_id']], [
                            'class' => 'link-body-emphasis text-decoration-none fw-semibold',
                        ]) ?>
                    </div>
                    <span class="badge rounded-pill text-bg-primary">
                        <?= (int) $item['quantity'] ?> sold
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>
