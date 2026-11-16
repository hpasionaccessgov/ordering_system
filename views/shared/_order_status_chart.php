<?php

use app\assets\ChartJsAsset;
use app\models\Order;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/** @var View $this */
/** @var array<string, int> $orderStatusCounts */

ChartJsAsset::register($this);

$statuses = [
    Order::STATUS_PENDING => 'Pending',
    Order::STATUS_CONFIRMED => 'Confirmed',
    Order::STATUS_PROCESSING => 'Processing',
    Order::STATUS_COMPLETED => 'Completed',
    Order::STATUS_CANCELLED => 'Cancelled',
];
$labels = array_values($statuses);
$values = array_map(
    static fn (string $status): int => (int) ($orderStatusCounts[$status] ?? 0),
    array_keys($statuses),
);
$chartData = Json::htmlEncode([
    'labels' => $labels,
    'values' => $values,
]);

$this->registerJs(<<<JS
const statusChartCanvas = document.getElementById('order-status-chart');
if (statusChartCanvas && window.Chart) {
    const chartData = {$chartData};
    const textColor = getComputedStyle(document.documentElement)
        .getPropertyValue('--bs-body-color')
        .trim();

    new window.Chart(statusChartCanvas, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Orders',
                data: chartData.values,
                backgroundColor: ['#c98c5d', '#a96a3d', '#89431c', '#56805d', '#a44a3f'],
                borderRadius: 6,
                maxBarThickness: 48
            }]
        },
        options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => `\${context.parsed.x} orders`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: textColor },
                    grid: { color: 'rgba(127, 127, 127, .15)' }
                },
                y: {
                    ticks: { color: textColor },
                    grid: { display: false }
                }
            }
        }
    });
}
JS, View::POS_END);
?>

<section class="card cafebara-dashboard-card h-100">
    <div class="card-header bg-transparent">
        <h2 class="h5 mb-1">Orders by status</h2>
        <p class="small text-body-secondary mb-0">Current workload across the order workflow</p>
    </div>
    <div class="card-body">
        <div class="cafebara-chart-container">
            <canvas
                id="order-status-chart"
                role="img"
                aria-label="Order counts grouped by status">
            </canvas>
        </div>
        <ul class="visually-hidden">
            <?php foreach ($statuses as $status => $label) : ?>
                <li><?= Html::encode($label) ?>: <?= Html::encode((string) ($orderStatusCounts[$status] ?? 0)) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
