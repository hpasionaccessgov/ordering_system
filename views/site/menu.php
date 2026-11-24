<?php

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int, string> $categories */
/** @var string $search */
/** @var string $categoryId */
/** @var string $filters */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */
/** @var list<array{product_id: int, product_name: string, quantity: int}> $bestSellers */

$this->title = 'Menu';
$batchFormId = (!Yii::$app->user->isGuest && Yii::$app->user->can('cart.add'))
    ? 'menu-batch-cart'
    : null;
?>

<section class="py-4 py-lg-5 cafebara-menu-page">
    <div class="p-4 p-lg-5 mb-4 rounded-4 cafebara-menu-hero">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <p class="mb-2 cafebara-section-kicker">Cafébara menu</p>
                <h1 class="mb-2 fw-bold display-6">Find your next favorite.</h1>
                <p class="mb-4">Browse active drinks and bites. Sign in to add items to your cart.</p>
                <?php if (Yii::$app->user->isGuest) : ?>
                    <?= Html::a('Sign in to order', ['/site/login'], [
                        'class' => 'btn btn-cafebara-light',
                    ]) ?>
                <?php endif; ?>
            </div>
            <div class="col-lg-4 text-center">
                <?= Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
                    'class' => 'cafebara-menu-logo',
                    'alt' => 'Cafébara capybara mascot',
                ]) ?>
            </div>
        </div>
    </div>

    <?= Html::beginForm(['/site/menu'], 'get', [
        'class' => 'catalog-filter p-3 mb-4',
        'role' => 'search',
    ]) ?>
    <div class="row g-2">
        <div class="col-12 col-md">
            <?= Html::input('search', 'q', $search, [
                'class' => 'form-control',
                'placeholder' => 'Search the menu',
                'aria-label' => 'Search menu products',
            ]) ?>
        </div>
        <div class="col-12 col-md-4">
            <?= Html::dropDownList('category_id', $categoryId, [
                '' => 'All categories',
            ] + $categories, [
                'class' => 'form-select',
                'aria-label' => 'Filter menu by category',
            ]) ?>
        </div>
        <div class="col-12 col-md-auto d-flex gap-2">
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Clear', ['/site/menu'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    <?= $this->render('../shared/_listing_filters', [
        'filters' => $filters,
        'showAudit' => false,
        'showDates' => false,
        'showAmount' => true,
        'amountLabel' => '',
    ]) ?>
    <?= Html::endForm() ?>

    <?php if ($batchFormId !== null && $dataProvider->getCount() > 0) : ?>
        <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($dataProvider->getModels() as $product) : ?>
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

    <?= $this->render('../shared/_best_sellers', ['bestSellers' => $bestSellers ?? []]) ?>

    <?php if ($dataProvider->getCount() === 0) : ?>
        <div class="card admin-panel">
            <div class="py-5 card-body text-center">
                <h2 class="h5">No products match your search</h2>
                <p class="mb-0 text-body-secondary">Try another name or category.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex mt-4 justify-content-center">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
        ]) ?>
    </div>
</section>