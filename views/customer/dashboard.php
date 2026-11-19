<?php

/** @var yii\web\View $this */
/** @var app\models\Product[] $featuredProducts */
/** @var int $orderCount */

use app\models\User;
use yii\helpers\Html;

$this->title = 'Customer dashboard';
$batchFormId = Yii::$app->user->can('cart.add') ? 'customer-batch-cart' : null;
$identity = Yii::$app->user->identity;
$displayName = $identity instanceof User
    ? ($identity->full_name ?: $identity->username)
    : 'there';
?>

<section class="card dashboard-welcome-card dashboard-welcome-customer border-0 mb-4">
    <div class="card-body row align-items-center g-4">
        <div class="col">
            <span class="dashboard-welcome-role mb-3">
                <i class="bi bi-person-heart" aria-hidden="true"></i> Customer
            </span>
            <h1 class="h3 mt-2 mb-2">Welcome, <?= Html::encode($displayName) ?>!</h1>
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

<section class="customer-summary card border-0 mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <p class="text-body-secondary mb-1">Your order history</p>
            <h2 class="h4 mb-0"><?= Html::encode((string) $orderCount) ?> orders placed</h2>
        </div>
        <?php if (Yii::$app->user->can('order.index')) : ?>
            <?= Html::a('View your orders', ['/order/index'], ['class' => 'btn btn-outline-primary ms-auto']) ?>
        <?php endif; ?>
    </div>
</section>

<?php if (Yii::$app->user->can('product.view')) : ?>
<section class="mb-4">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <p class="text-body-secondary mb-1">From the menu</p>
            <h2 class="h4 mb-0">Featured drinks and bites</h2>
        </div>
        <?php if (Yii::$app->user->can('product.index')) : ?>
            <?= Html::a('Browse all products', ['/product/index'], ['class' => 'link-primary fw-semibold']) ?>
        <?php endif; ?>
    </div>
    <?php if ($batchFormId !== null && $featuredProducts !== []) : ?>
        <?= $this->render('../shared/_batch_cart', ['batchFormId' => $batchFormId]) ?>
    <?php endif; ?>
    <div class="row g-3">
        <?php foreach ($featuredProducts as $product) : ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <?= $this->render('../shared/_product_card', [
                    'product' => $product,
                    'batchFormId' => $batchFormId,
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
