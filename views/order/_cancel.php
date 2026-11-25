<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $model */
/** @var string|null $returnUrl */
/** @var string $buttonClass */
/** @var string $buttonLabel */

$returnUrl = $returnUrl ?? Yii::$app->request->get('returnUrl');
$buttonClass = $buttonClass ?? 'btn btn-outline-danger';
$buttonLabel = $buttonLabel ?? 'Cancel order';

$canCancel = Yii::$app->user->can('order.cancel')
    && (
        Yii::$app->user->can('order.process')
            ? $model->canBeCancelledByStaff()
            : $model->canBeCancelledByCustomer()
    );
?>

<?php if ($canCancel) : ?>
    <?= Html::beginForm(['/sales/orders/' . $model->id . '/cancel'], 'POST', ['class' => 'd-inline']) ?>
    <?php if (is_string($returnUrl) && str_starts_with($returnUrl, '/')) : ?>
        <?= Html::hiddenInput('returnUrl', $returnUrl) ?>
    <?php endif; ?>
    <?= Html::submitButton($buttonLabel, [
        'class' => $buttonClass,
        'data-confirm-modal' => 'Cancel this order?',
    ]) ?>
    <?= Html::endForm() ?>
<?php endif; ?>