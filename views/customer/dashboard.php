<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $featuredDataProvider */
/** @var int $orderCount */
/** @var float $totalSpent */
/** @var int $favoriteCount */
/** @var app\models\Product|null $favoriteProduct */
/** @var app\models\Order|null $recentOrder */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */
/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */

use app\models\User;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

$this->title = 'Customer dashboard';
$batchFormId = Yii::$app->user->can('cart.add') ? 'customer-batch-cart' : null;
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
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
                    <?= Html::a('Browse menu', ['/product/index'], ['class' => 'btn btn-cafebara-light']) ?>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('order.index')) : ?>
                    <?= Html::a('View orders', ['/order/index'], ['class' => 'btn btn-cafebara-outline-light']) ?>
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
        ['Total orders', (string) ($orderCount ?? 0), 'bi-receipt', 'All statuses'],
        ['Total spent', Yii::$app->formatter->asCurrency($totalSpent ?? 0), 'bi-cash-coin', 'Completed orders only'],
        ['Favorites', (string) ($favoriteCount ?? 0), 'bi-heart', 'Saved products'],
        ['Favorite product', ($favoriteProduct ?? null) !== null ? $favoriteProduct->name : 'No favorites yet', 'bi-star', 'Most recently added'],
    ] as [$label, $value, $icon, $caption]) : ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <section class="h-100 small-box cafebara-stat-card">
                <div class="inner">
                    <p><?= Html::encode($label) ?></p>
                    <?php if ($label === 'Favorite product' && ($favoriteProduct ?? null) !== null) : ?>
                        <h3 class="cafebara-stat-value">
                            <?= Html::a(Html::encode($favoriteProduct->name), ['/product/view', 'id' => $favoriteProduct->id], [
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
</div>

<section class="mb-4 card admin-panel">
    <div class="d-flex flex-wrap gap-3 card-body justify-content-between align-items-center">
        <div>
            <p class="mb-1 text-body-secondary">Most recent order</p>
            <?php if (($recentOrder ?? null) !== null) : ?>
                <h2 class="mb-0 h5">
                    <?= Html::encode($recentOrder->order_number) ?>
                    <span class="text-body-secondary fw-normal">
                        · <?= Html::encode(ucfirst((string) $recentOrder->status)) ?>
                        · <?= Yii::$app->formatter->asDatetime($recentOrder->created_at) ?>
                    </span>
                </h2>
            <?php else : ?>
                <h2 class="mb-0 h5">No orders yet</h2>
            <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-2 ms-auto">
            <?php if (Yii::$app->user->can('favorite.index')) : ?>
                <?= Html::a('My favorites', ['/favorite/index'], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
            <?php if (Yii::$app->user->can('order.index')) : ?>
                <?= Html::a('View your orders', ['/order/index'], ['class' => 'btn btn-primary']) ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (Yii::$app->user->can('product.view')) : ?>
    <section class="mb-4">
        <div class="d-flex mb-3 justify-content-between align-items-end">
            <div>
                <p class="mb-1 text-body-secondary">From the menu</p>
                <h2 class="mb-0 h4">Featured drinks and bites</h2>
            </div>
            <?php if (Yii::$app->user->can('product.index')) : ?>
                <?= Html::a('Browse all products', ['/product/index'], ['class' => 'link-primary fw-semibold']) ?>
            <?php endif; ?>
        </div>
        <?php if ($batchFormId !== null && $featuredDataProvider->getCount() > 0) : ?>
            <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
        <?php endif; ?>
        <div class="row g-3">
            <?php foreach ($featuredDataProvider->getModels() as $product) : ?>
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
        <div class="d-flex mt-4 justify-content-center">
            <?= LinkPager::widget([
                'pagination' => $featuredDataProvider->pagination,
                'prevPageLabel' => 'Previous',
                'nextPageLabel' => 'Next',
            ]) ?>
        </div>
    </section>
<?php endif; ?>

<?= $this->render('../shared/_best_sellers', ['bestSellers' => $bestSellers ?? []]) ?>