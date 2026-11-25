<?php

use app\models\Reward;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var Reward $model */
?>
<section class="card admin-panel">
    <div class="card-body">
        <?php $form = AdminActiveForm::begin(); ?>
        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
        <?= $form->field($model, 'point_cost')->input('number', ['min' => 1]) ?>
        <?= $form->field($model, 'status')->dropDownList([
            Reward::STATUS_ACTIVE => 'Active',
            Reward::STATUS_INACTIVE => 'Disabled',
        ]) ?>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <?= Html::submitButton('Save reward', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php AdminActiveForm::end(); ?>
    </div>
</section>
