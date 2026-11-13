<?php

declare(strict_types=1);

namespace app\widgets;

use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class AdminActiveForm extends ActiveForm
{
    public function init()
    {
        Html::addCssClass($this->options, 'cafebara-admin-form');

        $this->fieldConfig = ArrayHelper::merge([
            'options' => ['class' => 'mb-3'],
            'labelOptions' => ['class' => 'form-label fw-semibold'],
            'errorOptions' => ['class' => 'invalid-feedback d-block'],
        ], $this->fieldConfig);

        parent::init();
    }
}