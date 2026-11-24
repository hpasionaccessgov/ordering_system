<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

$currentRoute = Yii::$app->controller->route;
$navigationItems = [
    ['label' => 'Home', 'route' => 'site/index', 'url' => ['/site/index']],
    ['label' => 'Menu', 'route' => 'site/menu', 'url' => ['/site/menu']],
    // ['label' => 'About', 'route' => 'site/about', 'url' => ['/site/about']],
    ['label' => 'Contact', 'route' => 'site/contact', 'url' => ['/site/contact']],
];
$user = Yii::$app->user->identity;
$hasDashboardAccess = Yii::$app->user->can('admin.access')
    || Yii::$app->user->can('staff.access')
    || Yii::$app->user->can('customer.access');
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
<header id="header" class="cafebara-site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
        <div class="container">
            <?= Html::a($brand, ['/site/index'], [
                'class' => 'navbar-brand cafebara-brand-lockup d-flex align-items-center gap-2',
                'aria-label' => 'Cafébara home',
            ]) ?>

            <?= Html::button('<i class="bi bi-list" aria-hidden="true"></i>', [
                'class' => 'navbar-toggler btn btn-outline-primary',
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

                <div class="d-flex gap-2 align-items-center cafebara-nav-actions">
                    <?= $this->render('_theme_toggle') ?>

                    <?php if (Yii::$app->user->isGuest): ?>
                        <?= Html::a('Sign in', ['/site/login'], [
                            'class' => 'btn btn-outline-primary',
                        ]) ?>
                        <?= Html::a('Get started', ['/site/register'], [
                            'class' => 'btn btn-primary',
                        ]) ?>
                    <?php else: ?>
                        <?php if (Yii::$app->user->can('favorite.index')): ?>
                            <?= Html::a('<i class="bi bi-heart" aria-hidden="true"></i><span class="ms-1 d-none d-xl-inline">Favorites</span>', ['/favorite/index'], [
                                'class' => 'btn btn-outline-primary',
                                'encode' => false,
                                'aria-label' => 'Favorites',
                            ]) ?>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('notification.index')): ?>
                            <?= $this->render('_notifications_link', ['class' => 'btn btn-outline-primary']) ?>
                        <?php endif ?>
                        <?= Html::a(
                            $hasDashboardAccess ? 'Dashboard' : 'Home',
                            $hasDashboardAccess ? ['/site/dashboard'] : ['/site/index'],
                            [
                            'class' => 'btn btn-primary',
                        ]) ?>
                        <?= Html::a('Profile', ['/site/profile'], [
                            'class' => 'btn btn-outline-primary',
                        ]) ?>
                        <?= Html::beginForm(['/site/logout'], 'POST', ['class' => 'm-0']) ?>
                        <?= Html::submitButton('Sign out', ['class' => 'btn btn-link cafebara-sign-out']) ?>
                        <?= Html::endForm() ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </nav>
</header>