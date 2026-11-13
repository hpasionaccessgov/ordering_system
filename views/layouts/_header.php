<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;
use app\widgets\AdminButton;

$currentRoute = Yii::$app->controller->route;
$navigationItems = [
    ['label' => 'Home', 'route' => 'site/index', 'url' => ['/site/index']],
    ['label' => 'About', 'route' => 'site/about', 'url' => ['/site/about']],
    ['label' => 'Contact', 'route' => 'site/contact', 'url' => ['/site/contact']],
];
$user = Yii::$app->user->identity;
$brand = Html::img(Yii::getAlias('@web/images/Cafebara_logo.png'), [
    'alt' => '',
    'class' => 'cafebara-brand-logo',
]) . Html::tag(
    'span',
    Html::tag('span', 'Cafébara', ['class' => 'cafebara-brand-name'])
        . Html::tag('span', 'CAFE & ORDERING', ['class' => 'cafebara-brand-caption']),
    ['class' => 'cafebara-brand-copy'],
);
?>
<header id="header" class="cafebara-site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
        <div class="container">
            <?= Html::a($brand, ['/site/index'], [
                'class' => 'navbar-brand cafebara-brand-lockup d-flex align-items-center gap-2',
                'aria-label' => 'Cafébara home',
            ]) ?>

            <?= Html::button('<span class="cafebara-menu-icon" aria-hidden="true"></span>', [
                'class' => 'navbar-toggler',
                'type' => 'button',
                'data-bs-toggle' => 'collapse',
                'data-bs-target' => '#cafebara-public-nav',
                'aria-controls' => 'cafebara-public-nav',
                'aria-expanded' => 'false',
                'aria-label' => 'Toggle navigation',
            ]) ?>

            <div class="collapse navbar-collapse" id="cafebara-public-nav">
                <ul class="navbar-nav mx-auto cafebara-main-nav">
                    <?php foreach ($navigationItems as $item): ?>
                        <?php $active = $item['route'] === $currentRoute; ?>
                        <li class="nav-item">
                            <?= Html::a($item['label'], $item['url'], [
                                'class' => 'nav-link' . ($active ? ' active' : ''),
                                'aria-current' => $active ? 'page' : null,
                            ]) ?>
                        </li>
                    <?php endforeach ?>
                </ul>

                <div class="cafebara-nav-actions d-flex align-items-center gap-2">
                    <?= $this->render('_theme_toggle') ?>

                    <?php if (Yii::$app->user->isGuest): ?>
                        <?= Html::a('Sign in', ['/site/login'], [
                            'class' => 'btn btn-outline-primary',
                        ]) ?>
                        <?= Html::a('Get started', ['/site/register'], [
                            'class' => 'btn btn-primary',
                        ]) ?>
                    <?php else: ?>
                        <?= Html::a('Dashboard', ['/site/dashboard'], [
                            'class' => 'btn btn-primary',
                        ]) ?>
                        <?= Html::a('Profile', ['/site/profile'], [
                            'class' => 'btn btn-outline-primary',
                        ]) ?>
                        <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'm-0']) ?>
                        <?= AdminButton::submit('Sign out', 'link', ['class' => 'cafebara-sign-out']) ?>
                        <?= Html::endForm() ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </nav>
</header>