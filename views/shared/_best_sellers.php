<?php

use yii\helpers\Html;

/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */

$bestSellers = $bestSellers ?? [];
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
                <li class="list-group-item d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <?= Html::a(Html::encode($item['product_name']), ['/product/view', 'id' => $item['product_id']], [
                        'class' => 'link-body-emphasis text-decoration-none fw-semibold',
                    ]) ?>
                    <span class="badge rounded-pill text-bg-primary">
                        <?= (int) $item['quantity'] ?> sold
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>
