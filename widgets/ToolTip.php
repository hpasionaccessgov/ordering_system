<?php
use yii\helpers\Html;
use yii\web\JsExpression;

/* @var $this yii\web\View */

$this->title = 'Tooltip Example';
$this->params['breadcrumbs'][] = $this->title;

// Register Bootstrap tooltip initialization
$this->registerJs(
    new JsExpression('
        $(function () {
            $("[data-toggle=\'tooltip\']").tooltip();
        });
    ')
);
?>

<div class="site-tooltip">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Simple tooltip -->
    <?= Html::tag('p', 
        'Hover over this text to see a tooltip.', 
        ['data-toggle' => 'tooltip', 'title' => 'This is a tooltip message!']
    ) ?>

    <!-- Tooltip on a button -->
    <?= Html::button('Hover me', [
        'class' => 'btn btn-primary',
        'data-toggle' => 'tooltip',
        'title' => 'Button tooltip text'
    ]) ?>
</div>
