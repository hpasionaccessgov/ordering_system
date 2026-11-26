<?php

declare(strict_types=1);

use app\models\Order;
use yii\helpers\Html;
use yii\web\View;

/** @var yii\web\View $this */
/** @var array<string, list<Order>> $columns */
/** @var array<string, int> $counts */
/** @var int $completedToday */
/** @var float|null $averagePreparationMinutes */
/** @var list<string> $statuses */

$this->title = 'Kitchen Order Board';
$this->params['breadcrumbs'][] = $this->title;

$columnMeta = [
    Order::STATUS_PENDING => ['label' => 'Pending', 'tone' => 'pending'],
    Order::STATUS_CONFIRMED => ['label' => 'Confirmed', 'tone' => 'confirmed'],
    Order::STATUS_PROCESSING => ['label' => 'Processing', 'tone' => 'processing'],
    Order::STATUS_READY_FOR_PICKUP => ['label' => 'Ready For Pickup', 'tone' => 'ready'],
];

$warningSeconds = Order::PRIORITY_WARNING_MINUTES * 60;
$criticalSeconds = Order::PRIORITY_CRITICAL_MINUTES * 60;
$this->registerJs(<<<JS
(() => {
    const warningSeconds = {$warningSeconds};
    const criticalSeconds = {$criticalSeconds};
    const formatElapsed = (seconds) => String(Math.floor(seconds / 60)).padStart(2, '0') + ':'
        + String(seconds % 60).padStart(2, '0');

    function refreshKitchenTimers() {
        document.querySelectorAll('.kitchen-ticket[data-confirmed-at]').forEach((ticket) => {
            const confirmedAt = Number(ticket.dataset.confirmedAt);
            const value = ticket.querySelector('[data-timer-value]');
            if (!Number.isFinite(confirmedAt) || confirmedAt <= 0 || value === null) {
                return;
            }

            const elapsed = Math.max(0, Math.floor(Date.now() / 1000) - confirmedAt);
            const level = elapsed >= criticalSeconds ? 'critical'
                : elapsed >= warningSeconds ? 'warning'
                : 'normal';
            value.textContent = formatElapsed(elapsed);
            ticket.dataset.priority = level;
            ticket.querySelector('.kitchen-ticket-timer').dataset.level = level;
            const flag = ticket.querySelector('.kitchen-ticket-flag');
            if (flag !== null) {
                flag.dataset.level = level;
                flag.textContent = level === 'normal' ? '' : level === 'warning' ? 'Warning' : 'Critical';
                flag.hidden = level === 'normal';
            }
        });
    }

    refreshKitchenTimers();
    setInterval(refreshKitchenTimers, 1000);
})();
JS, View::POS_READY);
?>
<div class="kitchen-board">
    <header class="kitchen-board-header mb-4">
        <div>
            <p class="kitchen-board-kicker mb-1">Staff operations</p>
            <h1 class="kitchen-board-title mb-0"><?= Html::encode($this->title) ?></h1>
        </div>
        <p class="kitchen-board-stats mb-0">
            <span>Timer starts at confirmation · under 15 min · 15–29 min · 30+ min</span>
            <span class="kitchen-board-stat-divider" aria-hidden="true">·</span>
            <span><strong><?= Html::encode((string) $completedToday) ?></strong> completed today</span>
            <?php if ($averagePreparationMinutes !== null) : ?>
                <span class="kitchen-board-stat-divider" aria-hidden="true">·</span>
                <span><strong><?= Html::encode((string) $averagePreparationMinutes) ?></strong> min average prep</span>
            <?php endif; ?>
        </p>
    </header>

    <div class="row g-3 kitchen-board-rails">
        <?php foreach ($statuses as $status) : ?>
            <?php
            $meta = $columnMeta[$status] ?? ['label' => Order::statusLabel($status), 'tone' => 'pending'];
            $railId = 'kitchen-rail-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($status));
            $count = (int) ($counts[$status] ?? 0);
            ?>
            <div class="col-12 col-md-6 col-xl-3">
                <section class="kitchen-rail h-100" data-tone="<?= Html::encode($meta['tone']) ?>" aria-labelledby="<?= Html::encode($railId) ?>">
                    <header class="kitchen-rail-header">
                        <h2 class="kitchen-rail-title mb-0" id="<?= Html::encode($railId) ?>">
                            <span class="kitchen-rail-dot" aria-hidden="true"></span>
                            <?= Html::encode($meta['label']) ?>
                        </h2>
                        <span class="kitchen-rail-count"><?= $count ?> <span class="visually-hidden">tickets</span></span>
                    </header>
                    <div class="kitchen-rail-body">
                        <?php foreach ($columns[$status] ?? [] as $order) : ?>
                            <?= $this->render('_order_card', ['order' => $order]) ?>
                        <?php endforeach; ?>
                        <?php if (($columns[$status] ?? []) === []) : ?>
                            <p class="kitchen-rail-empty mb-0">No tickets on this rail.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        <?php endforeach; ?>
    </div>
</div>
