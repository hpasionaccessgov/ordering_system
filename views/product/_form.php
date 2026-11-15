<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\Product $model */

$form = ActiveForm::begin([
    'options' => ['enctype' => 'multipart/form-data'],
]);
?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'description')->textarea(['rows' => 5]) ?>

<?= $form->field($model, 'category')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'imageFile')->fileInput([
    'accept' => 'image/png,image/jpeg,image/webp',
])->hint('Stored securely in the database. Maximum size: 3 MB.') ?>

<?= $form->field($model, 'is_featured')->checkbox() ?>

<?= $form->field($model, 'price')->input('number', [
    'step' => '0.01',
    'min' => '0',
]) ?>

<?= $form->field($model, 'status')->dropDownList([
    \app\models\Product::STATUS_ACTIVE => 'Active',
    \app\models\Product::STATUS_ARCHIVED => 'Archived',
]) ?>

<div>
    <?= Html::submitButton('Save product', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
