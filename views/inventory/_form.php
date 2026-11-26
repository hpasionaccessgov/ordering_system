<?php

use app\services\Navigation;
use app\models\Inventory;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Inventory $model */
/** @var AdminActiveForm $form */
?>
<section class="card admin-panel">
    <div class="card-body">
        <?php $form = AdminActiveForm::begin() ?>
        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
        <?= $form->field($model, 'unit')->textInput(['maxlength' => true, 'placeholder' => 'Liter, piece, cup...']) ?>
        <div class="row g-3">
            <div class="col-sm-6"><?= $form->field($model, 'current_stock')->input('number', ['min' => 0]) ?></div>
            <div class="col-sm-6"><?= $form->field($model, 'minimum_stock')->input('number', ['min' => 0]) ?></div>
        </div>
        <?= $form->field($model, 'status')->dropDownList(Inventory::statusList()) ?>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <?= Html::submitButton($model->isNewRecord ? 'Create item' : 'Save item', ['class' => 'btn btn-primary']) ?>
            <?= Html::a(
                'Cancel',
                Yii::$app->user->can('inventory.index') ? ['index'] : Navigation::url(Navigation::DASHBOARD),
                ['class' => 'btn btn-outline-secondary'],
            ) ?>
        </div>
        <?php AdminActiveForm::end() ?>
    </div>
</section>
