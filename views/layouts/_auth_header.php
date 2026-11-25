<?php

declare(strict_types=1);

use yii\helpers\Html;

$actionId = Yii::$app->controller->action->id;
$otherAuthRoute = $actionId === 'login' ? ['/site/register'] : ['/site/login'];
$otherAuthLabel = $actionId === 'login' ? 'Create account' : 'Sign in';
$brand = Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
    'alt' => '',
    'class' => 'cafebara-brand-logo',
]) . Html::tag(
    'span',
    Html::tag('span', 'Cafébara', ['class' => 'cafebara-brand-name'])
        . Html::tag('span', 'CAFE & ORDERING', ['class' => 'cafebara-brand-caption']),
    ['class' => 'cafebara-brand-copy'],
);
?>
<header class="cafebara-auth-header">
    <nav class="navbar" aria-label="Account navigation">
        <div class="container">
            <?= Html::a($brand, ['/site/index'], [
                'class' => 'navbar-brand cafebara-brand-lockup d-flex align-items-center gap-2',
                'aria-label' => 'Cafébara home',
            ]) ?>

            <div class="d-flex gap-2 align-items-center ms-auto">
                <?= $this->render('_theme_toggle') ?>
                <?= Html::a('Home', ['/site/index'], ['class' => 'btn btn-outline-primary']) ?>
                <?= Html::a($otherAuthLabel, $otherAuthRoute, ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </nav>
</header>
