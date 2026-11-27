<?php

use app\services\Navigation;
/** @var yii\web\View $this */
/** @var int $userCount */
/** @var int $activeUserCount */
/** @var int $productCount */
/** @var int $pendingOrderCount */
/** @var int $inventoryCount */
/** @var int $lowStockCount */
/** @var int $outOfStockCount */
/** @var \app\models\Inventory[] $lowStockItems */
/** @var array<string, int> $orderStatusCounts */
/** @var array{revenue: float, orders: int, completed: int, cancelled: int, aov: float} $todaySales */

use app\models\User;
use yii\helpers\Html;

$this->title = 'Admin dashboard';
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
?>

<section class="card dashboard-welcome-card dashboard-welcome-admin border-0 mb-4">
    <div class="card-body row align-items-center g-4">
        <div class="col">
            <span class="dashboard-welcome-role mb-3">
                <i class="bi bi-shield-check" aria-hidden="true"></i> Administration
            </span>
            <h1 class="h3 mt-2 mb-2">Welcome, <?= Html::encode($displayName) ?></h1>
            <p class="mb-3">Manage access, keep the catalog healthy, and monitor cafe operations.</p>
            <div class="d-flex flex-wrap gap-2">
                <?php if (Yii::$app->user->can('user.index')) : ?>
                    <?= Html::a('Manage people', Navigation::url(Navigation::ADMIN_USER_INDEX), ['class' => 'btn btn-cafebara-light']) ?>
                <?php elseif (Yii::$app->user->can('user.create')) : ?>
                    <?= Html::a('Create account', Navigation::url(Navigation::ADMIN_USER_CREATE), ['class' => 'btn btn-cafebara-light']) ?>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('user.assignRole')) : ?>
                    <?= Html::a('Review roles', Navigation::url(Navigation::ADMIN_ROLE_INDEX), ['class' => 'btn btn-cafebara-outline-light']) ?>
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

<?= $this->render('../shared/_inventory_alert', ['lowStockItems' => $lowStockItems]) ?>

<div class="row g-3 mb-4">
    <?php foreach (
        [
            ["Today's revenue", Yii::$app->formatter->asCurrency($todaySales['revenue']), 'bi-cash-coin', 'Completed orders today'],
            ["Today's orders", $todaySales['orders'], 'bi-receipt', 'All statuses today'],
            ['Pending orders', $pendingOrderCount, 'bi-hourglass-split', 'Need attention'],
            ['Current low stock', $lowStockCount, 'bi-exclamation-triangle', 'Below minimum'],
        ] as [$label, $value, $icon, $caption]
    ) : ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="small-box cafebara-stat-card h-100">
                <div class="inner">
                    <p><?= Html::encode($label) ?></p>
                    <h3 class="cafebara-stat-value"><?= Html::encode((string) $value) ?></h3>
                    <span><?= Html::encode($caption) ?></span>
                </div>
                <i class="small-box-icon bi <?= Html::encode($icon) ?>" aria-hidden="true"></i>
            </section>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <?= $this->render('../shared/_order_status_chart', [
            'orderStatusCounts' => $orderStatusCounts,
        ]) ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <?= $this->render('../shared/_product_highlights', [
            'featuredProducts' => $featuredProducts ?? [],
            'specialProducts' => $specialProducts ?? [],
            'favoriteIds' => $favoriteIds ?? [],
            'ratingSummaries' => $ratingSummaries ?? [],
        ]) ?>
    </div>
    <div class="col-12 col-xl-4 d-flex flex-column">
        <?= $this->render('../shared/_best_sellers', ['bestSellers' => $bestSellers ?? []]) ?>
    </div>
</div>
