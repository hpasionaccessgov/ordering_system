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
        ['/sales/orders/' . $model->id . '/cancel'],
        'POST',
        ['class' => 'd-inline'],
    ) ?>
    <?php $returnUrl = Yii::$app->request->get('returnUrl'); ?>
    <?php if (is_string($returnUrl) && str_starts_with($returnUrl, '/')) : ?>
        <?= Html::hiddenInput('returnUrl', $returnUrl) ?>
    <?php endif; ?>

    <?= Html::submitButton(
        'Cancel order',
        [
            'class' => 'btn btn-outline-danger',
            'data-confirm-modal' => 'Cancel this order?',
        ],
    ) ?>

    <?= Html::endForm() ?>
<?php endif; ?>