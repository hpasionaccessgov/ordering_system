<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $featuredDataProvider */
/** @var int $orderCount */
/** @var float $totalSpent */
/** @var int $favoriteCount */
/** @var app\models\Product|null $favoriteProduct */
/** @var list<app\models\Order> $recentOrders */
/** @var list<app\models\Notification> $recentNotifications */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */
/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */
/** @var array{current: int, lifetimeEarned: int, lifetimeRedeemed: int, tier: string} $loyalty */

use app\models\Order;
use app\models\User;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

$this->title = 'Customer dashboard';
$batchFormId = Yii::$app->user->can('cart.add') ? 'customer-batch-cart' : null;
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
$loyalty = $loyalty ?? ['current' => 0, 'lifetimeEarned' => 0, 'lifetimeRedeemed' => 0, 'tier' => 'bronze'];
?>
<section class="mb-4 card border-0 dashboard-welcome-card dashboard-welcome-customer">
    <div class="row g-4 card-body align-items-center">
        <div class="col">
            <span class="mb-3 dashboard-welcome-role">
                <i class="bi bi-person-heart" aria-hidden="true"></i> Customer
            </span>
            <h1 class="mt-2 mb-2 h3">Welcome, <?= Html::encode($displayName) ?>!</h1>
            <p class="mb-3">Find a favorite, place an order, and follow its progress.</p>
            <div class="d-flex flex-wrap gap-2">
                <?php if (Yii::$app->user->can('product.index')) : ?>
                    <?= Html::a('Browse menu', ['/site/menu'], ['class' => 'btn btn-cafebara-light']) ?>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order.index')) : ?>
                    <?= Html::a('View orders', ['/sales/orders'], ['class' => 'btn btn-cafebara-outline-light']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-auto d-none d-sm-block">
            <?= Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
                'class' => 'dashboard-welcome-logo',
                'alt' => '',
                'aria-hidden' => 'true',
            ]) ?>
        </div>
    </div>
</section>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['Current Points', Yii::$app->formatter->asInteger($loyalty['current'] ?? 0), 'bi-stars', 'Ready to redeem'],
        ['Total Orders', (string) ($orderCount ?? 0), 'bi-receipt', 'All statuses'],
        ['Total Amount Spent', Yii::$app->formatter->asCurrency($totalSpent ?? 0), 'bi-cash-coin', 'Completed orders'],
        [
            'Favorite Product',
            ($favoriteProduct ?? null) !== null ? $favoriteProduct->name : 'No favorites yet',
            'bi-heart',
            (int) ($favoriteCount ?? 0) . ' saved',
        ],
    ] as [$label, $value, $icon, $caption]) : ?>
        <div class="col-6 col-lg-4 col-xxl-3">
            <section class="h-100 small-box cafebara-stat-card cafebara-kpi-card">
                <div class="inner">
                    <p><?= Html::encode($label) ?></p>
                    <?php if ($label === 'Favorite Product' && ($favoriteProduct ?? null) !== null) : ?>
                        <h3 class="cafebara-stat-value">
                            <?= Html::a(Html::encode($favoriteProduct->name), ['/catalog/products/' . $favoriteProduct->id], [
                                'class' => 'link-body-emphasis text-decoration-none',
                            ]) ?>
                        </h3>
                    <?php else : ?>
                        <h3 class="cafebara-stat-value"><?= Html::encode((string) $value) ?></h3>
                    <?php endif; ?>
                    <span><?= Html::encode($caption) ?></span>
                </div>
                <i class="small-box-icon bi <?= Html::encode($icon) ?>" aria-hidden="true"></i>
            </section>
        </div>
    <?php endforeach; ?>
    <?php if (Yii::$app->user->can('loyalty.view')) : ?>
        <div class="col-6 col-lg-4 col-xxl-3">
            <section class="h-100 small-box cafebara-stat-card cafebara-kpi-card">
                <div class="inner">
                    <p>Current Loyalty Tier</p>
                    <h3 class="cafebara-stat-value">
                        <?= $this->render('../shared/_loyalty_badge', ['tier' => $loyalty['tier']]) ?>
                    </h3>
                    <span>Lifetime earned: <?= Yii::$app->formatter->asInteger($loyalty['lifetimeEarned'] ?? 0) ?></span>
                </div>
                <i class="small-box-icon bi bi-trophy" aria-hidden="true"></i>
            </section>
        </div>
    <?php endif; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
        <section class="card h-100 admin-panel dashboard-activity-panel">
            <div class="d-flex flex-wrap gap-2 card-header justify-content-between align-items-center">
                <h2 class="mb-0 h5">Recent orders</h2>
                <?php if (Yii::$app->user->can('order.index')) : ?>
                    <?= Html::a('View all', ['/sales/orders'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                <?php endif; ?>
            </div>
            <?php if (($recentOrders ?? []) === []) : ?>
                <div class="card-body text-body-secondary">
                    No orders yet. Order your first drink today.
                </div>
            <?php else : ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentOrders as $order) : ?>
                        <?php /** @var Order $order */ ?>
                        <?= Html::a(
                            '<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">'
                                . '<span><strong>' . Html::encode($order->order_number) . '</strong>'
                                . '<small class="d-block text-body-secondary">'
                                . Html::encode(ucfirst((string) $order->status)) . ' · '
                                . Html::encode(Yii::$app->formatter->asDatetime($order->created_at))
                                . '</small></span>'
                                . '<strong>' . Html::encode(Yii::$app->formatter->asCurrency($order->total_amount)) . '</strong>'
                                . '</div>',
                            ['/sales/orders/' . $order->id],
                            ['class' => 'list-group-item list-group-item-action'],
                        ) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-12 col-lg-6">
        <section class="card h-100 admin-panel dashboard-activity-panel">
            <div class="d-flex flex-wrap gap-2 card-header justify-content-between align-items-center">
                <h2 class="mb-0 h5">Recent notifications</h2>
                <?php if (Yii::$app->user->can('notification.index')) : ?>
                    <?= Html::a('View all', ['/notifications'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                <?php endif; ?>
            </div>
            <?php if (($recentNotifications ?? []) === []) : ?>
                <div class="card-body text-body-secondary">
                    No notifications yet. Your order and reward updates will appear here.
                </div>
            <?php else : ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentNotifications as $notification) : ?>
                        <?php /** @var app\models\Notification $notification */ ?>
                        <?= Html::a(
                            '<div class="d-flex gap-3 align-items-start">'
                                . '<span class="fs-5 text-body-secondary" aria-hidden="true">'
                                . '<i class="bi ' . Html::encode($notification->categoryIconClass) . '"></i></span>'
                                . '<span><span class="d-block">' . Html::encode($notification->label) . '</span>'
                                . '<small class="text-body-secondary">'
                                . Html::encode(Yii::$app->formatter->asDatetime($notification->created_at))
                                . '</small></span></div>',
                            ['/notification/view', 'id' => $notification->id],
                            ['class' => 'list-group-item list-group-item-action'],
                        ) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php if (Yii::$app->user->can('loyalty.view')) : ?>
    <section class="mb-4 card cafebara-dashboard-card">
        <div class="d-flex flex-wrap gap-2 card-header justify-content-between align-items-center">
            <h2 class="mb-0 h5">Your loyalty journey</h2>
            <?= Html::a('Open My Rewards', ['/loyalty/index'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-center text-center">
                <div class="col-6 col-xl-2">
                    <p class="mb-1 text-body-secondary small">Tier</p>
                    <?= $this->render('../shared/_loyalty_badge', ['tier' => $loyalty['tier']]) ?>
                </div>
                <?php foreach ([
                    ['Current points', $loyalty['current']],
                    ['Lifetime earned', $loyalty['lifetimeEarned']],
                    ['Lifetime redeemed', $loyalty['lifetimeRedeemed']],
                ] as [$label, $value]) : ?>
                    <div class="col-6 col-xl-2">
                        <p class="mb-1 text-body-secondary small"><?= Html::encode($label) ?></p>
                        <p class="mb-0 h4"><?= Yii::$app->formatter->asInteger($value) ?></p>
                    </div>
                <?php endforeach; ?>
                <div class="col-12 col-xl-4">
                    <p class="mb-1 text-body-secondary small">Next treat</p>
                    <p class="mb-0">
                        <?php if ($loyalty['current'] >= 50) : ?>
                            You have enough points for a free coffee.
                        <?php else : ?>
                            <?= Yii::$app->formatter->asInteger(50 - $loyalty['current']) ?> more points until a free coffee.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (Yii::$app->user->can('product.view')) : ?>
    <section class="mb-4">
        <div class="d-flex mb-3 justify-content-between align-items-end">
            <div>
                <p class="mb-1 text-body-secondary">From the menu</p>
                <h2 class="mb-0 h4">Featured drinks and bites</h2>
            </div>
            <?php if (Yii::$app->user->can('product.index')) : ?>
                <?= Html::a('Browse menu', ['/site/menu'], ['class' => 'link-primary fw-semibold']) ?>
            <?php endif; ?>
        </div>
        <?php if ($featuredDataProvider !== null && $batchFormId !== null && $featuredDataProvider->getCount() > 0) : ?>
            <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
        <?php endif; ?>
        <div class="row g-3">
            <?php foreach (($featuredDataProvider?->getModels() ?? []) as $product) : ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <?= $this->render('../shared/_product_card', [
                        'product' => $product,
                        'batchFormId' => $batchFormId,
                        'favoriteIds' => $favoriteIds ?? [],
                        'ratingSummaries' => $ratingSummaries ?? [],
                    ]) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($featuredDataProvider !== null) : ?>
            <div class="d-flex mt-4 justify-content-center">
                <?= LinkPager::widget([
                    'pagination' => $featuredDataProvider->pagination,
                    'prevPageLabel' => 'Previous',
                    'nextPageLabel' => 'Next',
                ]) ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?= $this->render('../shared/_best_sellers', ['bestSellers' => $bestSellers ?? []]) ?>
