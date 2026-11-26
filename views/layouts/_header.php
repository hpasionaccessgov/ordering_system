<?php

declare(strict_types=1);

use app\services\Navigation;

use app\services\LoyaltyService;
use yii\helpers\Html;

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
<nav class="navbar navbar-expand-md bg-body app-header" aria-label="Main navigation">
    <div class="container-fluid flex-wrap flex-md-nowrap">
        <?= Html::a($brand, Navigation::url(Navigation::HOME), [
            'class' => 'navbar-brand cafebara-admin-brand cafebara-brand-lockup d-flex align-items-center gap-2',
            'aria-label' => 'Cafébara home',
        ]) ?>

        <ul class="flex-row gap-1 ms-md-0 navbar-nav align-items-center ms-auto">
            <li class="nav-item">
                <?= Html::button(
                    Html::tag('i', '', ['class' => 'bi bi-list', 'aria-hidden' => 'true']),
                    [
                        'class' => 'btn btn-outline-primary cafebara-icon-btn cafebara-sidebar-toggle',
                        'type' => 'button',
                        'data-lte-toggle' => 'sidebar',
                        'aria-label' => 'Toggle sidebar',
                    ],
                ) ?>
            </li>
            <li class="d-md-none nav-item">
                <?= Html::button(
                    Html::tag('i', '', ['class' => 'bi bi-three-dots', 'aria-hidden' => 'true']),
                    [
                        'class' => 'btn btn-outline-primary cafebara-icon-btn',
                        'type' => 'button',
                        'title' => 'Toggle account menu',
                        'data-bs-toggle' => 'collapse',
                        'data-bs-target' => '#cafebara-admin-nav',
                        'aria-controls' => 'cafebara-admin-nav',
                        'aria-expanded' => 'false',
                        'aria-label' => 'Toggle account menu',
                    ],
                ) ?>
            </li>
        </ul>

        <div class="collapse navbar-collapse" id="cafebara-admin-nav">
            <ul class="d-flex flex-row flex-wrap gap-2 ms-md-auto mb-0 list-unstyled align-items-center cafebara-admin-actions">
                <?php if (Yii::$app->user->isGuest): ?>
                    <li class="nav-item">
                        <?= Html::a(
                            '<i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>'
                                . Html::tag('span', 'Sign in', ['class' => 'cafebara-btn-label d-none d-md-inline']),
                            Navigation::url(Navigation::LOGIN),
                            ['class' => 'btn btn-outline-primary cafebara-icon-btn', 'encode' => false, 'aria-label' => 'Sign in'],
                        ) ?>
                    </li>
                <?php else: ?>
                    <?php if (Yii::$app->user->can('loyalty.view')): ?>
                        <?php $loyalty = LoyaltyService::summary((int) Yii::$app->user->id); ?>
                        <li class="nav-item">
                            <?= $this->render('_theme_toggle') ?>
                        </li>
                        <li class="nav-item">
                            <?= $this->render('_cart_button') ?>
                        </li>
                        <li class="nav-item dropdown">
                            <?= Html::button(
                                Html::tag('i', '', ['class' => 'bi bi-gift', 'aria-hidden' => 'true'])
                                    . Html::tag('span', $this->render('../shared/_loyalty_badge', [
                                        'tier' => $loyalty['tier'],
                                    ]), ['class' => 'd-none d-md-inline'])
                                    . Html::tag('span', Yii::$app->formatter->asInteger($loyalty['current']) . ' pts', [
                                        'class' => 'cafebara-btn-label d-none d-md-inline',
                                    ]),
                                [
                                    'class' => 'btn btn-outline-primary dropdown-toggle cafebara-icon-btn',
                                    'type' => 'button',
                                    'data-bs-toggle' => 'dropdown',
                                    'data-bs-auto-close' => 'outside',
                                    'aria-haspopup' => 'true',
                                    'aria-expanded' => 'false',
                                    'aria-label' => 'My rewards: ' . $loyalty['current']
                                        . ' points, ' . LoyaltyService::tierLabel($loyalty['tier']) . ' tier',
                                ],
                            ) ?>
                            <ul class="dropdown-menu dropdown-menu-end cafebara-header-menu" data-bs-popper="static">
                                <li>
                                    <span class="dropdown-header text-body-secondary">
                                        <i class="bi bi-gift me-2" aria-hidden="true"></i>Your loyalty snapshot
                                    </span>
                                </li>
                                <?php foreach (
                                    [
                                        'Current points' => Yii::$app->formatter->asInteger($loyalty['current']) . ' pts',
                                        'Lifetime earned' => Yii::$app->formatter->asInteger($loyalty['lifetimeEarned']) . ' pts',
                                        'Lifetime redeemed' => Yii::$app->formatter->asInteger($loyalty['lifetimeRedeemed']) . ' pts',
                                        'Tier' => LoyaltyService::tierLabel($loyalty['tier']),
                                    ] as $label => $value
                                ): ?>
                                    <li class="border-bottom border-primary-subtle">
                                        <span class="dropdown-item-text d-flex justify-content-between gap-3 small">
                                            <span class="text-body-secondary"><?= Html::encode($label) ?></span>
                                            <span class="fw-semibold text-end"><?= Html::encode($value) ?></span>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><?= Html::a('Open My Rewards', Navigation::url(Navigation::REWARDS), ['class' => 'dropdown-item']) ?></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (Yii::$app->user->can('notification.index')): ?>
                        <li class="nav-item">
                            <?= $this->render('_notifications_link', [
                                'class' => 'btn btn-outline-primary cafebara-icon-btn',
                            ]) ?>
                        </li>
                    <?php endif ?>


                    <li class="nav-item">
                        <?= $this->render('_profile_dropdown', ['class' => 'btn btn-outline-primary']) ?>
                    </li>
                <?php endif ?>
            </ul>
        </div>
    </div>
</nav>