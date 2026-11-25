<?php

use app\models\Category;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Category $model */
/** @var AdminActiveForm $form */
?>

<section class="card admin-panel">
    <div class="card-body">
        <?php $form = AdminActiveForm::begin(); ?>
        <?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
        <?= $form->field($model, 'status')->dropDownList([
            Category::STATUS_ACTIVE => 'Active',
            Category::STATUS_INACTIVE => 'Inactive',
        ]) ?>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <?= Html::submitButton('Save category', ['class' => 'btn btn-primary']) ?>
            <?= Html::a(
                'Cancel',
                Yii::$app->user->can('category.index') ? ['index'] : ['/site/dashboard'],
                ['class' => 'btn btn-secondary'],
            ) ?>
        </div>
        <?php AdminActiveForm::end(); ?>
    </div>
</section>
