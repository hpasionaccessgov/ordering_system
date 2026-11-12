<?php

declare(strict_types=1);

use yii\helpers\Html;

$user = Yii::$app->user->identity;
?>
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <?= Html::a(
            Html::img(Yii::getAlias('@web/images/Cafebara_logo.png'), [
                'alt' => '',
                'class' => 'cafebara-brand-logo',
            ]) . Html::tag('span', 'Cafébara', ['class' => 'cafebara-brand-name d-none d-sm-inline']),
            ['/site/index'],
            [
                'class' => 'navbar-brand cafebara-admin-brand d-flex align-items-center gap-2',
                'aria-label' => 'Cafébara home',
            ],
        ) ?>

        <ul class="navbar-nav">
            <li class="nav-item">
                <a
                    class="nav-link"
                    data-lte-toggle="sidebar"
                    href="#"
                    role="button"
                    aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <?php if (Yii::$app->user->isGuest): ?>
                <li class="nav-item">
                    <?= Html::a('Login', ['/site/login'], ['class' => 'nav-link']) ?>
                </li>
            <?php else: ?>
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
                        <li>
                            <?= Html::a('Change password', ['/site/change-password'], [
                                'class' => 'dropdown-item',
                            ]) ?>
                        </li>
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