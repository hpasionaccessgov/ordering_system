<?php

declare(strict_types=1);

use app\services\Navigation;

/** @var yii\web\View $this */

use yii\helpers\Html;

$currentRoute = Yii::$app->controller->route;
$navigationItems = [
    ['label' => 'Home', 'route' => 'site/index', 'url' => Navigation::url(Navigation::HOME)],
    ['label' => 'Menu', 'route' => 'site/menu', 'url' => Navigation::url(Navigation::MENU)],
    // ['label' => 'About', 'route' => 'site/about', 'url' => Navigation::url(Navigation::ABOUT)],
    ['label' => 'Contact', 'route' => 'site/contact', 'url' => Navigation::url(Navigation::CONTACT)],
];
$hasDashboardAccess = Yii::$app->user->can('admin.access')
    || Yii::$app->user->can('staff.access')
    || Yii::$app->user->can('customer.access');
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
<header id="header" class="cafebara-site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
        <div class="container">
            <?= Html::a($brand, Navigation::url(Navigation::HOME), [
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

                <div class="d-flex flex-wrap gap-2 align-items-center cafebara-nav-actions">
                    <?= $this->render('_theme_toggle') ?>
                    <?= $this->render('_cart_button') ?>

                    <?php if (Yii::$app->user->isGuest): ?>
                        <?= Html::a(
                            '<i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>'
                                . Html::tag('span', 'Sign in', ['class' => 'cafebara-btn-label d-none d-sm-inline']),
                            Navigation::url(Navigation::LOGIN),
                            ['class' => 'btn btn-outline-primary cafebara-icon-btn', 'encode' => false, 'aria-label' => 'Sign in'],
                        ) ?>
                        <?= Html::a(
                            '<i class="bi bi-person-plus" aria-hidden="true"></i>'
                                . Html::tag('span', 'Get started', ['class' => 'cafebara-btn-label d-none d-sm-inline']),
                            Navigation::url(Navigation::REGISTER),
                            ['class' => 'btn btn-primary cafebara-icon-btn', 'encode' => false, 'aria-label' => 'Get started'],
                        ) ?>
                    <?php else: ?>
                        <?php if (Yii::$app->user->can('favorite.index')): ?>
                            <?= Html::a(
                                '<i class="bi bi-heart" aria-hidden="true"></i>'
                                    . Html::tag('span', 'Favorites', ['class' => 'cafebara-btn-label d-none d-lg-inline']),
                                Navigation::url(Navigation::FAVORITE_INDEX),
                                ['class' => 'btn btn-outline-primary cafebara-icon-btn', 'encode' => false, 'aria-label' => 'Favorites'],
                            ) ?>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('notification.index')): ?>
                            <?= $this->render('_notifications_link', [
                                'class' => 'btn btn-outline-primary cafebara-icon-btn',
                            ]) ?>
                        <?php endif ?>
                        <?= Html::a(
                            '<i class="bi bi-speedometer2" aria-hidden="true"></i>'
                                . Html::tag('span', $hasDashboardAccess ? 'Dashboard' : 'Home', [
                                    'class' => 'cafebara-btn-label d-none d-md-inline',
                                ]),
                            $hasDashboardAccess ? Navigation::url(Navigation::DASHBOARD) : Navigation::url(Navigation::HOME),
                            [
                                'class' => 'btn btn-primary cafebara-icon-btn',
                                'encode' => false,
                                'aria-label' => $hasDashboardAccess ? 'Dashboard' : 'Home',
                            ],
                        ) ?>
                        <?= $this->render('_profile_dropdown', ['class' => 'btn btn-outline-primary']) ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </nav>
</header>