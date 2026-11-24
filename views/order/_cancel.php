<?php

use app\models\Order;
use yii\helpers\Html;

/** @var Order $model */

$canCancel = Yii::$app->user->can('order.cancel')
    && (
        Yii::$app->user->can('order.process')
            ? $model->canBeCancelledByStaff()
            : $model->canBeCancelledByCustomer()
    );
?>

<?php if ($canCancel): ?>
    <?= Html::beginForm(
        ['/order/cancel', 'id' => $model->id],
        'POST',
        ['class' => 'd-inline'],
    ) ?>

    <?= Html::submitButton(
        'Cancel order',
        [
            'class' => 'btn btn-outline-danger',
            'data-confirm-modal' => 'Cancel this order?',
        ],
    ) ?>

    <?= Html::endForm() ?>
<?php endif; ?>