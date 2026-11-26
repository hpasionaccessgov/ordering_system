<?php

declare(strict_types=1);

use app\services\Navigation;

use yii\helpers\Html;

$actionId = Yii::$app->controller->action->id;
$otherAuthRoute = $actionId === 'login' ? Navigation::url(Navigation::REGISTER) : Navigation::url(Navigation::LOGIN);
$otherAuthLabel = $actionId === 'login' ? 'Create account' : 'Sign in';
$otherAuthIcon = $actionId === 'login' ? 'bi-person-plus' : 'bi-box-arrow-in-right';
$brand = Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
    'alt' => '',
    'class' => 'cafebara-brand-logo',
    'width' => 38,
    'height' => 38,
]) . Html::tag(
    'span',
    Html::tag('span', 'Cafébara', ['class' => 'cafebara-brand-name'])
        . Html::tag('span', 'Café & ordering', ['class' => 'cafebara-brand-caption']),
    ['class' => 'cafebara-brand-copy'],
);
?>
<header class="cafebara-auth-header">
    <nav class="navbar" aria-label="Account navigation">
        <div class="container">
            <?= Html::a($brand, Navigation::url(Navigation::HOME), [
                'class' => 'navbar-brand cafebara-brand-lockup d-flex align-items-center gap-2',
                'aria-label' => 'Cafébara home',
            ]) ?>

            <div class="d-flex gap-2 align-items-center ms-auto cafebara-auth-actions">
                <?= $this->render('_theme_toggle') ?>
                <?= Html::a(
                    '<i class="bi bi-house" aria-hidden="true"></i>'
                        . Html::tag('span', 'Home', ['class' => 'cafebara-btn-label d-none d-sm-inline']),
                    Navigation::url(Navigation::HOME),
                    ['class' => 'btn btn-outline-primary cafebara-icon-btn', 'encode' => false, 'aria-label' => 'Home'],
                ) ?>
                <?= Html::a(
                    '<i class="bi ' . $otherAuthIcon . '" aria-hidden="true"></i>'
                        . Html::tag('span', $otherAuthLabel, ['class' => 'cafebara-btn-label d-none d-sm-inline']),
                    $otherAuthRoute,
                    ['class' => 'btn btn-primary cafebara-icon-btn', 'encode' => false, 'aria-label' => $otherAuthLabel],
                ) ?>
            </div>
        </div>
    </nav>
</header>
