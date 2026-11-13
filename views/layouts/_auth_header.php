<?php

declare(strict_types=1);

use yii\helpers\Html;

$actionId = Yii::$app->controller->action->id;
$otherAuthRoute = $actionId === 'login' ? ['/site/register'] : ['/site/login'];
$otherAuthLabel = $actionId === 'login' ? 'Create account' : 'Sign in';
?>
<header class="cafebara-auth-header">
    <nav class="navbar">
        <div class="container">
            <?= Html::a(
                Html::img(Yii::getAlias('@web/images/Cafebara_logo.png'), [
                    'alt' => '',
                    'class' => 'cafebara-brand-logo',
                ]) . Html::tag('span', 'Cafébara', ['class' => 'cafebara-brand-name']),
                ['/site/index'],
                [
                    'class' => 'navbar-brand d-flex align-items-center gap-2',
                    'aria-label' => 'Cafébara home',
                ],
            ) ?>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <?= $this->render('_theme_toggle') ?>
                <?= Html::a('Home', ['/site/index'], ['class' => 'btn btn-outline-primary']) ?>
                <?= Html::a($otherAuthLabel, $otherAuthRoute, ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </nav>
</header>
