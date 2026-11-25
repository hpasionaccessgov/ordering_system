<?php

declare(strict_types=1);

use app\models\LoyaltyPointTransaction;
use app\models\Reward;
use app\services\LoyaltyService;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array{current: int, lifetimeEarned: int, lifetimeRedeemed: int, tier: string} $summary */
/** @var list<Reward> $rewards */
/** @var list<LoyaltyPointTransaction> $recentTransactions */
/** @var list<LoyaltyPointTransaction> $redemptionHistory */

$this->title = 'My Rewards';
$this->params['breadcrumbs'][] = $this->title;

$lifetime = $summary['lifetimeEarned'];
if ($lifetime >= 500) {
    $tierFloor = 500;
    $nextThreshold = 500;
    $nextTier = 'Gold';
    $progress = 100;
    $remaining = 0;
} elseif ($lifetime >= 100) {
    $tierFloor = 100;
    $nextThreshold = 500;
    $nextTier = 'Gold';
    $progress = (int) round(($lifetime - $tierFloor) / ($nextThreshold - $tierFloor) * 100);
    $remaining = $nextThreshold - $lifetime;
} else {
    $tierFloor = 0;
    $nextThreshold = 100;
    $nextTier = 'Silver';
    $progress = (int) round($lifetime / $nextThreshold * 100);
    $remaining = $nextThreshold - $lifetime;
}
$progress = max(0, min(100, $progress));
?>
<section class="mb-4 card border-0 dashboard-welcome-card dashboard-welcome-customer">
    <div class="row g-4 card-body align-items-center">
        <div class="col">
            <span class="dashboard-welcome-role">
                <i class="bi bi-gift" aria-hidden="true"></i> Loyalty
            </span>
            <h1 class="mt-2 mb-2 h3">Your rewards, <?= Html::encode(Yii::$app->user->identity?->full_name ?: Yii::$app->user->identity?->username ?: 'friend') ?>!</h1>
            <p class="mb-3">
                Earn 1 point for every ₱100 spent on completed orders. Use your points for the treats below.
            </p>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?= $this->render('../shared/_loyalty_badge', ['tier' => $summary['tier']]) ?>
                <span class="small">Current tier</span>
            </div>
        </div>
        <div class="col-md-5">
            <div class="p-3 rounded-4 bg-light text-dark">
                <div class="d-flex justify-content-between small fw-semibold">
                    <span><?= Html::encode(LoyaltyService::tierLabel($summary['tier'])) ?> tier</span>
                    <span><?= Html::encode($nextTier) ?></span>
                </div>
                <div class="progress mt-2" role="progressbar" aria-label="Progress to next tier"
                     aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-primary" style="width: <?= $progress ?>%"></div>
                </div>
                <p class="mt-2 mb-0 small text-body-secondary">
                    <?= $remaining > 0
                        ? Html::encode($remaining . ' more earned point' . ($remaining === 1 ? '' : 's') . ' to reach ' . $nextTier . '.')
                        : 'You reached the highest tier. Enjoy your perks!' ?>
                </p>
            </div>
        </div>
    </div>
</section>

<?= $this->render('../report/_summary_cards', ['summary' => [
    ['label' => 'Current points', 'value' => Yii::$app->formatter->asInteger($summary['current']), 'caption' => 'Available to spend', 'icon' => 'bi-coin'],
    ['label' => 'Lifetime earned', 'value' => Yii::$app->formatter->asInteger($summary['lifetimeEarned']), 'caption' => 'All completed orders', 'icon' => 'bi-arrow-up-circle'],
    ['label' => 'Lifetime redeemed', 'value' => Yii::$app->formatter->asInteger($summary['lifetimeRedeemed']), 'caption' => 'Points used so far', 'icon' => 'bi-gift'],
    ['label' => 'Current tier', 'value' => LoyaltyService::tierLabel($summary['tier']), 'caption' => 'Based on lifetime earned', 'icon' => 'bi-trophy'],
]]) ?>

<section class="mb-4">
    <div class="d-flex mb-3 justify-content-between align-items-end">
        <div>
            <p class="mb-1 text-body-secondary">Available treats</p>
            <h2 class="mb-0 h4">Redeem your points</h2>
        </div>
    </div>
    <?php if ($rewards === []) : ?>
        <div class="alert alert-info">No rewards are available right now. Check back soon.</div>
    <?php else : ?>
        <div class="row g-3">
            <?php foreach ($rewards as $reward) : ?>
                <?php $canRedeem = $summary['current'] >= (int) $reward->point_cost; ?>
                <div class="col-12 col-sm-6 col-xl-4">
                    <section class="card cafebara-dashboard-card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span class="d-inline-flex align-items-center justify-content-center rounded-3 cafebara-dashboard-icon cafebara-reward-icon">
                                    <i class="bi bi-gift" aria-hidden="true"></i>
                                </span>
                                <span class="badge text-bg-primary"><?= Yii::$app->formatter->asInteger($reward->point_cost) ?> pts</span>
                            </div>
                            <h3 class="h5 mt-3 mb-1"><?= Html::encode($reward->name) ?></h3>
                            <p class="text-body-secondary small flex-grow-1"><?= Html::encode($reward->description ?: 'A little something for your next visit.') ?></p>
                            <?php if ($canRedeem) : ?>
                                <?= Html::beginForm(['/loyalty/redeem', 'id' => $reward->id], 'POST', ['class' => 'mt-3']) ?>
                                <?= Html::submitButton('Redeem now', [
                                    'class' => 'btn btn-primary w-100',
                                    'data-confirm-modal' => 'Redeem ' . $reward->name . ' for ' . $reward->point_cost . ' points?',
                                ]) ?>
                                <?= Html::endForm() ?>
                            <?php else : ?>
                                <button class="btn btn-outline-secondary w-100 mt-3" type="button" disabled>
                                    Need <?= Yii::$app->formatter->asInteger((int) $reward->point_cost - $summary['current']) ?> more points
                                </button>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <section class="card cafebara-dashboard-card h-100">
            <div class="card-header">
                <h2 class="h5 mb-0">Reward history</h2>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped align-middle mb-0 cafebara-data-table">
                    <thead><tr><th>Reward</th><th>Points</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if ($redemptionHistory === []) : ?>
                            <tr><td colspan="3" class="text-center text-body-secondary py-4">No rewards redeemed yet.</td></tr>
                        <?php else : ?>
                            <?php foreach ($redemptionHistory as $transaction) : ?>
                                <tr>
                                    <td><?= Html::encode($transaction->remarks ?: 'Reward') ?></td>
                                    <td><?= Html::encode(Yii::$app->formatter->asInteger(abs((int) $transaction->points))) ?></td>
                                    <td><?= Yii::$app->formatter->asDatetime($transaction->created_at) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <div class="col-12 col-xl-6">
        <section class="card cafebara-dashboard-card h-100">
            <div class="card-header">
                <h2 class="h5 mb-0">Recent point activity</h2>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped align-middle mb-0 cafebara-data-table">
                    <thead><tr><th>Activity</th><th>Points</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if ($recentTransactions === []) : ?>
                            <tr><td colspan="3" class="text-center text-body-secondary py-4">Complete an order to start earning points.</td></tr>
                        <?php else : ?>
                            <?php foreach ($recentTransactions as $transaction) : ?>
                                <tr>
                                    <td>
                                        <?= Html::encode($transaction->typeLabel) ?>
                                        <?php if ($transaction->remarks) : ?>
                                            <span class="d-block text-body-secondary small"><?= Html::encode($transaction->remarks) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="<?= (int) $transaction->points > 0 ? 'text-success-emphasis' : 'text-danger-emphasis' ?>">
                                        <?= (int) $transaction->points > 0 ? '+' : '' ?><?= Yii::$app->formatter->asInteger($transaction->points) ?>
                                    </td>
                                    <td><?= Yii::$app->formatter->asDatetime($transaction->created_at) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
