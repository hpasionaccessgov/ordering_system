<?php

use app\models\Product;
use app\services\Navigation;
use yii\helpers\Html;

/** @var list<array{product_id: int, product_name: string, quantity: int, top_sellers_rank?: int}> $bestSellers */

$bestSellers = $bestSellers ?? [];
$products = Product::find()
    ->where(['id' => array_column($bestSellers, 'product_id')])
    ->indexBy('id')
    ->all();
?>
<section class="mb-4 card cafebara-dashboard-card best-sellers-panel">
    <div class="d-flex flex-wrap gap-2 card-header justify-content-between align-items-center">
        <h2 class="mb-0 h5">Best sellers</h2>
        <?php if ($bestSellers !== []) : ?>
            <span class="text-body-secondary fw-bold ms-auto small">Most ordered menu</span>
        <?php endif; ?>
    </div>
    <?php if ($bestSellers === []) : ?>
        <div class="card-body text-body-secondary">
            No completed orders yet.
        </div>
    <?php else : ?>
        <ol class="list-group list-group-flush best-seller-list">
            <?php foreach ($bestSellers as $index => $item) : ?>
                <?php
                $product = $products[(int) $item['product_id']] ?? null;
                $rank = (int) ($item['top_sellers_rank'] ?? ($index + 1));
                ?>
                <li class="p-0 list-group-item">
                    <?= Html::a(
                        ($product?->getImageUrl() !== null
                            ? Html::tag('span', '#' . $rank, [
                                'class' => 'best-seller-rank badge rounded-pill',
                                'data-rank' => $rank,
                                'aria-label' => 'Rank ' . $rank,
                            ]) . Html::img($product->getImageUrl(), [
                                'class' => 'best-seller-image',
                                'alt' => '',
                                'width' => 42,
                                'height' => 42,
                                'loading' => 'lazy',
                            ])
                            : Html::tag('span', '<i class="bi bi-cup-hot" aria-hidden="true"></i>', [
                                'class' => 'best-seller-image best-seller-placeholder',
                                'aria-hidden' => 'true',
                            ]))
                            . Html::tag('span', Html::encode($item['product_name']), ['class' => 'best-seller-name'])

                            . Html::tag(
                                'span',
                                (int) $item['quantity'] . ' sold',
                                ['class' => 'badge rounded-pill text-bg-primary best-seller-count'],
                            ),
                        Navigation::url(Navigation::PRODUCT_VIEW, ['id' => (int) $item['product_id']]),
                        ['class' => 'best-seller-link d-flex gap-3 align-items-center text-decoration-none', 'encode' => false],
                    ) ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>