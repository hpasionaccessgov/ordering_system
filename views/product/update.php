<?php

use yii\helpers\Html;

/** @var app\models\Product $model */

$this->title = 'Update product';
?>

<h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>

<?= $this->render('_form', ['model' => $model]) ?>