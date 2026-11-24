<?php

use yii\helpers\Html;

/** @var array<string, string> $summary */

$icons = ['bi-cash-coin', 'bi-receipt', 'bi-graph-up', 'bi-box-seam', 'bi-exclamation-triangle'];
$index = 0;
?>
<div class="row g-3 mb-4">
    <?php foreach ($summary as $label => $value) : ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="small-box cafebara-stat-card h-100">
                <div class="inner">
                    <p><?= Html::encode((string) $label) ?></p>
                    <h3 class="h5 mb-0 cafebara-stat-value"><?= Html::encode((string) $value) ?></h3>
                </div>
                <i class="small-box-icon bi <?= Html::encode($icons[$index++ % count($icons)]) ?>" aria-hidden="true"></i>
            </section>
        </div>
    <?php endforeach; ?>
</div>
