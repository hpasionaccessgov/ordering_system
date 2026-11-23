<?php

declare(strict_types=1);

use app\widgets\ToolTip;
use yii\helpers\Html;

$user = Yii::$app->user->identity;
?>
<nav class="navbar navbar-expand bg-body app-header">
    <div class="container-fluid">
        <?= Html::a(
            Html::tag('span', 'Cafébara', ['class' => 'cafebara-brand-name d-none d-sm-inline']),
            ['/site/index'],
            [
                'class' => 'navbar-brand cafebara-admin-brand d-flex align-items-center gap-2',
                'aria-label' => 'Cafébara home',
            ],
        ) ?>

        <ul class="navbar-nav">
            <li class="nav-item">
                <?= Html::a(
                    Html::tag('i', '', ['class' => 'bi bi-list', 'aria-hidden' => 'true']),
                    '#',
                    ToolTip::options('Toggle sidebar', [
                        'class' => 'nav-link',
                        'data-lte-toggle' => 'sidebar',
                        'role' => 'button',
                        'aria-label' => 'Toggle sidebar',
                    ]),
                ) ?>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <?php if (Yii::$app->user->isGuest): ?>
                <li class="nav-item">
                    <?= Html::a('Login', ['/site/login'], ['class' => 'nav-link']) ?>
                </li>
            <?php else: ?>
                <?php if (Yii::$app->user->can('notification.index')): ?>
                    <li class="nav-item">
                        <?= $this->render('_notifications_link', ['class' => 'nav-link']) ?>
                    </li>
                <?php endif ?>
                <li class="d-flex me-2 nav-item align-items-center">
                    <?= $this->render('_theme_toggle') ?>
                </li>
                <li class="nav-item dropdown">
                    <a
                        class="nav-link dropdown-toggle"
                        href="#"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <?= Html::encode($user?->username ?? 'Account') ?>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <?= Html::a('Profile', ['/site/profile'], ['class' => 'dropdown-item']) ?>
                        </li>
                        <!-- <li>
                            <?= Html::a('Change password', ['/site/change-password'], [
                                'class' => 'dropdown-item',
                            ]) ?>
                        </li> -->
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'm-0']) ?>
                            <?= Html::submitButton('Logout', ['class' => 'dropdown-item']) ?>
                            <?= Html::endForm() ?>
                        </li>
                    </ul>
                </li>
            <?php endif ?>
        </ul>
    </div>
</nav>