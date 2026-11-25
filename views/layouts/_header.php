<?php

declare(strict_types=1);

use app\widgets\ToolTip;
use yii\helpers\Html;

$user = Yii::$app->user->identity;
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
<nav class="navbar navbar-expand bg-body app-header" aria-label="Staff navigation">
    <div class="container-fluid">
        <?= Html::a($brand, ['/site/index'], [
            'class' => 'navbar-brand cafebara-admin-brand cafebara-brand-lockup d-flex align-items-center gap-2',
            'aria-label' => 'Cafébara home',
        ]) ?>

        <ul class="navbar-nav align-items-center">
            <li class="nav-item">
                <?= Html::a(
                    Html::tag('i', '', ['class' => 'bi bi-list', 'aria-hidden' => 'true']),
                    '#',
                    ToolTip::options('Toggle sidebar', [
                        'class' => 'btn btn-outline-primary cafebara-sidebar-toggle',
                        'data-lte-toggle' => 'sidebar',
                        'role' => 'button',
                        'aria-label' => 'Toggle sidebar',
                    ]),
                ) ?>
            </li>
        </ul>

        <ul class="gap-2 navbar-nav ms-auto align-items-center cafebara-admin-actions">
            <?php if (Yii::$app->user->isGuest): ?>
                <li class="nav-item">
                    <?= Html::a('Sign in', ['/site/login'], ['class' => 'btn btn-outline-primary']) ?>
                </li>
            <?php else: ?>
                <?php if (Yii::$app->user->can('notification.index')): ?>
                    <li class="nav-item">
                        <?= $this->render('_notifications_link', ['class' => 'btn btn-outline-primary']) ?>
                    </li>
                <?php endif ?>
                <li class="nav-item">
                    <?= $this->render('_theme_toggle') ?>
                </li>
                <li class="nav-item dropdown">
                    <?= Html::a(
                        Html::tag('i', '', [
                            'class' => 'bi bi-person-circle d-sm-none',
                            'aria-hidden' => 'true',
                        ]) . Html::tag('span', Html::encode($user?->username ?? 'Account'), [
                            'class' => 'd-none d-sm-inline',
                        ]),
                        '#',
                        [
                            'class' => 'btn btn-outline-primary dropdown-toggle',
                            'data-bs-toggle' => 'dropdown',
                            'aria-label' => 'Account: ' . ($user?->username ?? 'Account'),
                            'aria-expanded' => 'false',
                        ],
                    ) ?>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <?= Html::a('Profile', ['/profile'], ['class' => 'dropdown-item']) ?>
                        </li>
                        <!-- <li>
                            <?= Html::a('Change password', ['/profile/change-password'], [
                                'class' => 'dropdown-item',
                            ]) ?>
                        </li> -->
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <?= Html::beginForm(['/site/logout'], 'POST', ['class' => 'm-0']) ?>
                            <?= Html::submitButton('Logout', ['class' => 'dropdown-item']) ?>
                            <?= Html::endForm() ?>
                        </li>
                    </ul>
                </li>
            <?php endif ?>
        </ul>
    </div>
</nav>