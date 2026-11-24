<?php

declare(strict_types=1);

use app\models\ReportFilter;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var ReportFilter $filter */
/** @var array<int|string, string> $customerOptions */
/** @var bool $orderFilters */

$customerOptions ??= [];
$orderFilters ??= false;

$route = '/' . Yii::$app->controller->route;
$form = ActiveForm::begin([
    'method' => 'get',
    'action' => [$route],
    'options' => ['class' => 'card cafebara-dashboard-card mb-4'],
]);
?>
<div class="card-body">
    <div class="row g-3 align-items-end">
        <div class="col-sm-6 col-lg-3">
            <?= $form->field($filter, 'preset')->dropDownList([
                'today' => 'Today',
                'week' => 'This week',
                'month' => 'This month',
                'year' => 'This year',
                'custom' => 'Custom date range',
            ]) ?>
        </div>
        <div class="col-sm-6 col-lg-3">
            <?= $form->field($filter, 'from')->input('date') ?>
        </div>
        <div class="col-sm-6 col-lg-3">
            <?= $form->field($filter, 'to')->input('date') ?>
        </div>
        <?php if ($orderFilters) : ?>
            <div class="col-sm-6 col-lg-3">
                <?= $form->field($filter, 'status')->dropDownList(
                    ['' => 'All statuses'] + array_combine(
                        app\models\Order::statusValues(),
                        array_map('ucfirst', app\models\Order::statusValues()),
                    ),
                ) ?>
            </div>
            <div class="col-sm-6 col-lg-3">
                <?= $form->field($filter, 'customerId')->dropDownList(
                    ['' => 'All customers'] + $customerOptions,
                ) ?>
            </div>
        <?php endif; ?>
        <div class="col d-flex flex-wrap gap-2 pb-1">
            <?= Html::submitButton('Apply filters', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Reset', [$route], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    <p class="small text-body-secondary mb-0 mt-2">
        Start and end dates are only used with the Custom date range option.
    </p>
</div>
<?php ActiveForm::end(); ?>
