<?php

declare(strict_types=1);

use app\services\Navigation;
use yii\helpers\Html;
use yii\helpers\Url;

if (!Yii::$app->user->can('cart.index')) {
    return;
}
?>
<div
    class="offcanvas offcanvas-end cafebara-cart-drawer"
    tabindex="-1"
    id="cafebara-cart-offcanvas"
    aria-labelledby="cafebara-cart-title"
    data-cart-summary-url="<?= Html::encode(Url::to(Navigation::url(Navigation::CART_SUMMARY))) ?>"
>
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h5" id="cafebara-cart-title">Your cart</h2>
        <?= Html::button('<span aria-hidden="true">&times;</span>', [
            'class' => 'btn-close',
            'type' => 'button',
            'data-bs-dismiss' => 'offcanvas',
            'aria-label' => 'Close cart',
            'encode' => false,
        ]) ?>
    </div>
    <div class="offcanvas-body" id="cafebara-cart-body">
        <?= $this->render('_mini_cart_body') ?>
    </div>
</div>
