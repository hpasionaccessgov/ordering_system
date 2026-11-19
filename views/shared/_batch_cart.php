<?php

declare(strict_types=1);

use yii\helpers\Html;

/** @var string $batchFormId */
?>
<div class="cafebara-batch-cart">
    <?= Html::beginForm(['/cart/add-batch'], 'post', ['id' => $batchFormId]) ?>
    <?= Html::submitButton('Add selected to cart', ['class' => 'btn btn-primary btn-lg font-monospace']) ?>
    <?= Html::endForm() ?>
</div>
