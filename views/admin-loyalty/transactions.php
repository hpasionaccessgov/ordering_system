<?php

declare(strict_types=1);

use app\models\LoyaltyPointTransaction;
use app\widgets\AdminGridView;
use yii\helpers\Html;

/** @var app\models\LoyaltyTransactionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $title */

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'Loyalty', 'url' => ['/admin/loyalty']];
$this->params['breadcrumbs'][] = $this->title;

$route = '/' . Yii::$app->controller->route;
$isRedemptions = Yii::$app->controller->action->id === 'redemptions';
?>
<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Loyalty ledger</p>
        <h1 class="mb-0 h3"><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('All transactions', ['/admin/loyalty/transactions'], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::a('Redemptions', ['/admin/loyalty/redemptions'], ['class' => 'btn btn-outline-secondary']) ?>
        <?php if (Yii::$app->user->can('loyalty.adjust')) : ?>
            <?= Html::a('Adjust points', ['/admin/loyalty/adjust'], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>
</div>

<?= Html::beginForm([$route], 'get', ['class' => 'catalog-filter p-3 mb-4', 'role' => 'search']) ?>
<div class="row g-2">
    <div class="col-12 col-md">
        <?= Html::input('search', 'q', $searchModel->q, [
            'class' => 'form-control',
            'placeholder' => 'Search by customer, order, or remarks',
            'aria-label' => 'Search transactions',
        ]) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::input('number', 'user_id', $searchModel->user_id, [
            'class' => 'form-control',
            'placeholder' => 'Customer ID',
            'aria-label' => 'Customer ID',
            'min' => 1,
        ]) ?>
    </div>
    <?php if (!$isRedemptions) : ?>
        <div class="col-12 col-md-auto">
            <?= Html::dropDownList('transaction_type', $searchModel->transaction_type, ['' => 'All types'] + LoyaltyPointTransaction::typeLabels(), [
                'class' => 'form-select',
                'aria-label' => 'Transaction type',
            ]) ?>
        </div>
    <?php endif; ?>
    <div class="col-12 col-md-auto">
        <?= Html::input('date', 'created_from', $searchModel->created_from, ['class' => 'form-control', 'aria-label' => 'From date']) ?>
    </div>
    <div class="col-12 col-md-auto">
        <?= Html::input('date', 'created_to', $searchModel->created_to, ['class' => 'form-control', 'aria-label' => 'To date']) ?>
    </div>
    <div class="col-auto"><?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?></div>
    <div class="col-auto"><?= Html::a('Clear', [$route], ['class' => 'btn btn-outline-secondary']) ?></div>
</div>
<?= Html::errorSummary($searchModel, ['class' => 'alert alert-danger mt-2']) ?>
<?= Html::endForm() ?>

<section class="card admin-panel">
    <div class="card-header">
        <p class="mb-0 text-body-secondary">Every point change is kept as a permanent transaction record.</p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'user_id',
                'label' => 'Customer',
                'value' => static fn(LoyaltyPointTransaction $transaction): string =>
                    $transaction->user?->username ?? ('#' . $transaction->user_id),
            ],
            [
                'attribute' => 'transaction_type',
                'value' => static fn(LoyaltyPointTransaction $transaction): string => $transaction->typeLabel,
            ],
            [
                'attribute' => 'points',
                'value' => static fn(LoyaltyPointTransaction $transaction): string =>
                    ($transaction->points > 0 ? '+' : '') . Yii::$app->formatter->asInteger($transaction->points),
            ],
            'remarks',
            [
                'attribute' => 'order_id',
                'label' => 'Order',
                'value' => static fn(LoyaltyPointTransaction $transaction): string =>
                    $transaction->order?->order_number ?? '—',
            ],
            'created_at:datetime',
        ],
    ]) ?>
</section>
