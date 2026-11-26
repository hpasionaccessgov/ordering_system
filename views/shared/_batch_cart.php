<?php

declare(strict_types=1);

use app\services\Navigation;

use yii\helpers\Html;

/** @var string $batchFormId */
?>
<div class="cafebara-batch-cart">
    <?= Html::beginForm(Navigation::url(Navigation::CART_ADD_BATCH), 'POST', ['id' => $batchFormId]) ?>
    <?= Html::submitButton('Add selected to cart', ['class' => 'btn btn-primary font-monospace']) ?>
    <?= Html::endForm() ?>
</div>
