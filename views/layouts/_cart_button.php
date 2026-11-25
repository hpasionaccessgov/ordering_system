<?php

declare(strict_types=1);

use app\services\ShoppingCart;
use yii\helpers\Html;

if (!Yii::$app->user->can('cart.index')) {
    return;
}

$cartCount = (new ShoppingCart())->summary()['count'];
?>
<?= Html::button(
    '<i class="bi bi-cart3" aria-hidden="true"></i><span class="ms-1 d-none d-xl-inline">Cart</span>'
        . '<span class="badge rounded-pill text-bg-primary cafebara-cart-count'
        . ($cartCount === 0 ? ' d-none' : '') . '" data-cart-count>' . (int) $cartCount . '</span>',
    [
        'class' => 'btn btn-outline-primary cafebara-cart-button',
        'type' => 'button',
        'data-bs-toggle' => 'offcanvas',
        'data-bs-target' => '#cafebara-cart-offcanvas',
        'aria-controls' => 'cafebara-cart-offcanvas',
        'aria-label' => 'Open cart, ' . (int) $cartCount . ' item(s)',
    ],
) ?>
