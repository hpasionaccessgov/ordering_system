<?php

use app\services\Navigation;
use app\models\Product;
use yii\helpers\Html;

/** @var Product[] $products */

$this->title = 'Create order';
?>

<section class="order-builder-page">
    <div class="mb-4">
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Cafébara menu</p>
        <h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1>
        <p class="text-body-secondary mb-0">Choose quantities and review your order total before placing it.</p>
    </div>

    <?= $this->render('_product_selection', [
        'products' => $products,
        'quantities' => [],
        'formAction' => Navigation::url(Navigation::ORDER_CREATE),
        'submitLabel' => 'Place order',
    ]) ?>
</section>
