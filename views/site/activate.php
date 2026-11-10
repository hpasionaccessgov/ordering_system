<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var int $id */
/** @var string $token */

$this->title = 'Account Confirmation';
?>

<div class="site-activate">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Click below to activate your account.</p>

    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'action' => ['site/activate', 'id' => $id, 'token' => $token],
    ]); ?>

    <?= Html::submitButton('Activate account', ['class'=> 'btn btn-primary',
    ]) ?>

    <?php ActiveForm::end(); ?>
</div>
