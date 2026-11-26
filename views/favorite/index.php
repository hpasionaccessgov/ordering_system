<?php

use app\services\Navigation;
use yii\bootstrap5\LinkPager;
use yii\helpers\Html;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int, int> $favoriteIds */
/** @var array<int, array{average: float, count: int}> $ratingSummaries */

$this->title = 'My favorites';
$batchFormId = Yii::$app->user->can('cart.add') ? 'favorite-batch-cart' : null;
$products = [];
foreach ($dataProvider->getModels() as $favorite) {
    if ($favorite->product !== null) {
        $products[] = $favorite->product;
    }
}
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Saved for later</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
    <?php if (Yii::$app->user->can('product.index')) : ?>
        <?= Html::a('Browse menu', Navigation::url(Navigation::PRODUCT_INDEX), ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<?php if ($products === []) : ?>
    <div class="card admin-panel cafebara-empty-state">
        <div class="py-5 card-body text-center">
            <span class="cafebara-empty-icon" aria-hidden="true"><i class="bi bi-heart"></i></span>
            <h2 class="h5 mt-3">❤ No favorites yet.</h2>
            <p class="mb-3 text-body-secondary">Save your favorite drinks for quick ordering.</p>
            <?= Html::a('Browse Menu', Navigation::url(Navigation::MENU), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php else : ?>
    <?php if ($batchFormId !== null) : ?>
        <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
    <?php endif; ?>
    <div class="row g-3">
        <?php foreach ($products as $product) : ?>
            <div class="col-12 col-sm-6 col-xl-4">
                <?= $this->render('../shared/_product_card', [
                    'product' => $product,
                    'batchFormId' => $batchFormId,
                    'favoriteIds' => $favoriteIds,
                    'ratingSummaries' => $ratingSummaries,
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="d-flex mt-4 justify-content-center">
        <?= LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
        ]) ?>
    </div>
<?php endif; ?>
