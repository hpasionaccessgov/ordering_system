<?php

use yii\helpers\Html;

/** @var array{average: float, count: int}|null $summary */

$average = (float) ($summary['average'] ?? 0);
$count = (int) ($summary['count'] ?? 0);
?>
<?php if ($count === 0) : ?>
    <span class="product-rating small text-body-secondary">
        <i class="bi bi-star" aria-hidden="true"></i> No reviews yet
    </span>
<?php else : ?>
    <?php
    $rounded = round($average * 2) / 2;
    $full = (int) floor($rounded);
    $half = ($rounded - $full) >= 0.5 ? 1 : 0;
    $empty = max(0, 5 - $full - $half);
    $label = sprintf('Average rating %.1f out of 5 from %d review(s)', $average, $count);
    ?>
    <span class="product-rating d-inline-flex flex-wrap gap-1 align-items-center" role="img" aria-label="<?= Html::encode($label) ?>">
        <?php for ($i = 0; $i < $full; $i++) : ?>
            <i class="bi bi-star-fill text-warning" aria-hidden="true"></i>
        <?php endfor; ?>
        <?php if ($half === 1) : ?>
            <i class="bi bi-star-half text-warning" aria-hidden="true"></i>
        <?php endif; ?>
        <?php for ($i = 0; $i < $empty; $i++) : ?>
            <i class="bi bi-star text-body-secondary" aria-hidden="true"></i>
        <?php endfor; ?>
        <span class="ms-1 small text-body-secondary">
            <?= Html::encode(number_format($average, 1)) ?> (<?= $count ?>)
        </span>
    </span>
<?php endif; ?>
