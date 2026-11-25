<?php

use yii\helpers\Html;

/**
 * @var array<string, string>|list<array{label: string, value: string, caption?: string, icon?: string}> $summary
 */

$defaultIcons = ['bi-cash-coin', 'bi-receipt', 'bi-graph-up', 'bi-box-seam', 'bi-exclamation-triangle'];
$index = 0;
$cards = [];
foreach ($summary as $key => $item) {
    if (is_array($item)) {
        $cards[] = [
            'label' => (string) ($item['label'] ?? ''),
            'value' => (string) ($item['value'] ?? ''),
            'caption' => isset($item['caption']) ? (string) $item['caption'] : null,
            'icon' => (string) ($item['icon'] ?? $defaultIcons[$index % count($defaultIcons)]),
        ];
    } else {
        $cards[] = [
            'label' => (string) $key,
            'value' => (string) $item,
            'caption' => null,
            'icon' => $defaultIcons[$index % count($defaultIcons)],
        ];
    }
    $index++;
}
?>
<div class="row g-3 mb-4">
    <?php foreach ($cards as $card) : ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="small-box cafebara-stat-card h-100">
                <div class="inner">
                    <p><?= Html::encode($card['label']) ?></p>
                    <h3 class="h5 mb-0 cafebara-stat-value"><?= Html::encode($card['value']) ?></h3>
                    <?php if ($card['caption'] !== null && $card['caption'] !== '') : ?>
                        <span><?= Html::encode($card['caption']) ?></span>
                    <?php endif; ?>
                </div>
                <i class="small-box-icon bi <?= Html::encode($card['icon']) ?>" aria-hidden="true"></i>
            </section>
        </div>
    <?php endforeach; ?>
</div>
