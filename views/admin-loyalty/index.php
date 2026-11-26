<?php

declare(strict_types=1);

use app\services\Navigation;
use app\models\LoyaltyPointTransaction;
use app\services\LoyaltyService;
use yii\helpers\Html;

/** @var array{issued: int, redeemed: int, activeMembers: int} $summary */
/** @var list<array{tier: string, label: string, members: int}> $tiers */
/** @var list<LoyaltyPointTransaction> $recentTransactions */

$this->title = 'Loyalty overview';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Loyalty</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
        <p class="mb-0 text-body-secondary">Activity this year and quick access to loyalty records.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('Manage rewards', Navigation::url(Navigation::REWARD_INDEX), ['class' => 'btn btn-outline-secondary']) ?>
        <?php if (Yii::$app->user->can('loyalty.adjust')) : ?>
            <?= Html::a('Adjust points', Navigation::url(Navigation::LOYALTY_ADJUST), ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->render('../report/_summary_cards', ['summary' => [
    ['label' => 'Points issued', 'value' => Yii::$app->formatter->asInteger($summary['issued']), 'caption' => 'Earned this year', 'icon' => 'bi-coin'],
    ['label' => 'Points redeemed', 'value' => Yii::$app->formatter->asInteger($summary['redeemed']), 'caption' => 'Used this year', 'icon' => 'bi-gift'],
    ['label' => 'Active members', 'value' => Yii::$app->formatter->asInteger($summary['activeMembers']), 'caption' => 'With loyalty activity', 'icon' => 'bi-people'],
    ['label' => 'Tier report', 'value' => 'View', 'caption' => 'Bronze / Silver / Gold', 'icon' => 'bi-bar-chart-line'],
]]) ?>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-5">
        <section class="card cafebara-dashboard-card h-100">
            <div class="card-header"><h2 class="h5 mb-0">Tier distribution</h2></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($tiers as $tier) : ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= $this->render('../shared/_loyalty_badge', ['tier' => $tier['tier']]) ?>
                        <span class="fw-semibold"><?= Yii::$app->formatter->asInteger($tier['members']) ?> members</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="card-body">
                <?= Html::a('Open loyalty report', Navigation::url(Navigation::REPORT_LOYALTY), ['class' => 'btn btn-outline-primary']) ?>
            </div>
        </section>
    </div>
    <div class="col-12 col-lg-7">
        <section class="card cafebara-dashboard-card h-100">
            <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <h2 class="h5 mb-0">Recent loyalty activity</h2>
                <?= Html::a('All transactions', Navigation::url(Navigation::LOYALTY_TRANSACTIONS), ['class' => 'link-primary fw-semibold']) ?>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped align-middle mb-0 cafebara-data-table">
                    <thead><tr><th>Customer</th><th>Activity</th><th>Points</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if ($recentTransactions === []) : ?>
                            <tr><td colspan="4" class="text-center text-body-secondary py-4">No loyalty activity yet.</td></tr>
                        <?php else : ?>
                            <?php foreach ($recentTransactions as $transaction) : ?>
                                <tr>
                                    <td><?= Html::encode($transaction->user?->username ?? 'Deleted user') ?></td>
                                    <td><?= Html::encode($transaction->typeLabel) ?></td>
                                    <td><?= ($transaction->points > 0 ? '+' : '') . Yii::$app->formatter->asInteger($transaction->points) ?></td>
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

<p class="text-body-secondary small mb-0">
    Tier thresholds: Bronze 0–99, Silver 100–499, Gold 500+ lifetime earned points. Manual adjustments do not change tier.
</p>
