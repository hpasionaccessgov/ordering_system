<?php

declare(strict_types=1);

use app\assets\ChartJsAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/** @var yii\web\View $this */
/** @var string $id */
/** @var string $type */
/** @var string $title */
/** @var string $subtitle */
/** @var list<string> $labels */
/** @var list<array<string, mixed>> $datasets */
/** @var array<string, mixed> $options */

ChartJsAsset::register($this);

$config = [
    'type' => $type,
    'data' => ['labels' => $labels, 'datasets' => $datasets],
    'options' => array_replace_recursive([
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => ['legend' => ['display' => count($datasets) > 1]],
        'scales' => ['y' => ['beginAtZero' => true]],
    ], $options ?? []),
];
$idJson = Json::htmlEncode($id);
$configJson = Json::htmlEncode($config);
$js = <<<JS
(function () {
    const canvas = document.getElementById({$idJson});
    if (!canvas || !window.Chart) {
        return;
    }
    window.Chart.defaults.color = getComputedStyle(document.documentElement)
        .getPropertyValue('--bs-body-color')
        .trim() || '#666';
    new window.Chart(canvas, {$configJson});
})();
JS;
$this->registerJs($js, View::POS_END);
?>
<section class="card cafebara-dashboard-card h-100">
    <div class="card-header bg-transparent">
        <h2 class="h5 mb-1"><?= Html::encode($title) ?></h2>
        <p class="small text-body-secondary mb-0"><?= Html::encode($subtitle) ?></p>
    </div>
    <div class="card-body">
        <div class="cafebara-chart-container cafebara-report-chart">
            <canvas id="<?= Html::encode($id) ?>" role="img" aria-label="<?= Html::encode($title) ?>"></canvas>
        </div>
        <ul class="visually-hidden">
            <?php foreach ($labels as $index => $label) : ?>
                <li>
                    <?= Html::encode((string) $label) ?>:
                    <?php foreach ($datasets as $dataset) : ?>
                        <?= Html::encode((string) ($dataset['data'][$index] ?? '')) ?>
                    <?php endforeach; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
