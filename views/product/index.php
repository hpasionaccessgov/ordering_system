<?php

use app\services\Navigation;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int, string> $categories */
/** @var bool $canManageProducts */
/** @var bool $canViewProducts */
/** @var string $status */
/** @var string $search */
/** @var string $filters */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */
/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */

$this->title = 'Product';
$batchFormId = Yii::$app->user->can('cart.add') ? 'product-batch-cart' : null;
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-0">MENU AVAILABLE</p>
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
    </div>
    <?php if (Yii::$app->user->can('product.create')) : ?>
        <?= Html::a(
            'Create product',
            Navigation::url(Navigation::PRODUCT_CREATE),
            ['class' => 'btn btn-primary'],
        ) ?>
    <?php endif; ?>
</div>

<?php if ($canViewProducts) : ?>
    <?= $this->render('_search', [
        'categories' => $categories,
        'canManageProducts' => $canManageProducts,
        'status' => $status,
        'search' => $search,
        'filters' => $filters,
    ]) ?>
<?php else : ?>
    <div class="alert alert-warning mb-4" role="alert">
        You do not have permission to view products.
    </div>
<?php endif; ?>

<?= $this->render('../shared/_best_sellers', ['bestSellers' => $bestSellers ?? []]) ?>

<?php if ($canViewProducts) : ?>
    <?php if ($batchFormId !== null && $dataProvider->getCount() > 0) : ?>
        <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($dataProvider->getModels() as $product) : ?>
            <div class="col-12 col-sm-6 col-xl-4">
                <?= $this->render('../shared/_product_card', [
                    'product' => $product,
                    'batchFormId' => $batchFormId,
                    'favoriteIds' => $favoriteIds ?? [],
                    'ratingSummaries' => $ratingSummaries ?? [],
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($dataProvider->getCount() === 0) : ?>
        <div class="card admin-panel">
            <div class="card-body text-center py-5">
                <h2 class="h5">No products match your filters</h2>
                <p class="text-body-secondary mb-0">Try a different search or category.</p>
            </div>
        </div>
    <?php endif; ?>


    <div class="d-flex justify-content-center mt-4">
    <?= LinkPager::widget([
        
        'pagination' => $dataProvider->pagination,
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
    ]) ?>
    </div>
<?php endif; ?>