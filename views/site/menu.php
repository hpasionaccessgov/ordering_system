<?php

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int, string> $categories */
/** @var string $search */
/** @var string $categoryId */

$this->title = 'Menu';
$batchFormId = (!Yii::$app->user->isGuest && Yii::$app->user->can('cart.add'))
    ? 'menu-batch-cart'
    : null;
?>

<section class="cafebara-menu-page py-4 py-lg-5">
    <div class="cafebara-menu-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <p class="cafebara-section-kicker mb-2">Cafébara menu</p>
                <h1 class="display-6 fw-bold mb-2">Find your next favorite.</h1>
                <p class="mb-0">Browse active drinks and bites. Sign in to add items to your cart.</p>
            </div>
            <?php if (Yii::$app->user->isGuest) : ?>
                <div class="col-lg-4 text-lg-end">
                    <?= Html::a('Sign in to order', ['/site/login'], [
                        'class' => 'btn btn-cafebara-light',
                    ]) ?>
                </div>
            <?php endif; ?>
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
    <?= Html::endForm() ?>

    <div class="row g-3">
        <?php foreach ($dataProvider->getModels() as $product) : ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <?= $this->render('../shared/_product_card', [
                    'product' => $product,
                    'batchFormId' => $batchFormId,
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($batchFormId !== null && $dataProvider->getCount() > 0) : ?>
        <div class="cafebara-batch-cart">
            <?= Html::beginForm(['/cart/add-batch'], 'post', ['id' => $batchFormId]) ?>
            <?= Html::submitButton('Add selected to cart', ['class' => 'btn btn-primary btn-lg']) ?>
            <?= Html::endForm() ?>
        </div>
    <?php endif; ?>

    <?php if ($dataProvider->getCount() === 0) : ?>
        <div class="card admin-panel">
            <div class="card-body text-center py-5">
                <h2 class="h5">No products match your search</h2>
                <p class="text-body-secondary mb-0">Try another name or category.</p>
            </div>
        </div>
    <?php endif; ?>

    <?= LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
    ]) ?>
</section>
