<?php

declare(strict_types=1);

use yii\helpers\Html;

$currentRoute = Yii::$app->controller->route;
?>
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <?= Html::a(
            '<span class="brand-text fw-light">' . Html::encode(Yii::$app->name) . '</span>',
            ['/site/dashboard'],
            ['class' => 'brand-link'],
        ) ?>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-speedometer2"></i><p>Dashboard</p>',
                        ['/site/dashboard'],
                        ['class' => 'nav-link' . ($currentRoute === 'site/dashboard' ? ' active' : '')],
                    ) ?>
                </li>
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-person"></i><p>Profile</p>',
                        ['/site/profile'],
                        ['class' => 'nav-link' . ($currentRoute === 'site/profile' ? ' active' : '')],
                    ) ?>
                </li>
                <li class="nav-item">
                    <?= Html::a(
                        '<i class="nav-icon bi bi-key"></i><p>Change password</p>',
                        ['/site/change-password'],
                        ['class' => 'nav-link' . ($currentRoute === 'site/change-password' ? ' active' : '')],
                    ) ?>
                </li>
            </ul>
        </nav>
    </div>
</aside>