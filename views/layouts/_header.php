<?php

declare(strict_types=1);

use app\services\LoyaltyService;
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
<nav class="navbar navbar-expand-md bg-body app-header" aria-label="Main navigation">
    <div class="container-fluid flex-wrap flex-md-nowrap">
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

        <ul class="navbar-nav d-md-none">
            <li class="nav-item">
                <?= Html::button(
                    Html::tag('i', '', ['class' => 'bi bi-three-dots', 'aria-hidden' => 'true']),
                    [
                        'class' => 'btn btn-outline-primary',
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
            <ul class="gap-2 navbar-nav ms-auto align-items-center cafebara-admin-actions">
            <?php if (Yii::$app->user->isGuest): ?>
                <li class="nav-item">
                    <?= Html::a('Sign in', ['/site/login'], ['class' => 'btn btn-outline-primary']) ?>
                </li>
            <?php else: ?>
                <?php if (Yii::$app->user->can('loyalty.view')): ?>
                    <?php $loyalty = LoyaltyService::summary((int) Yii::$app->user->id); ?>
                    <li class="nav-item dropdown">
                        <?= Html::button(
                            $this->render('../shared/_loyalty_badge', ['tier' => $loyalty['tier']])
                                . ' ' . Yii::$app->formatter->asInteger($loyalty['current']) . ' pts',
                            [
                                'class' => 'btn btn-outline-primary dropdown-toggle text-nowrap',
                                'data-bs-toggle' => 'dropdown',
                                'aria-expanded' => 'false',
                                'aria-label' => 'My rewards: ' . $loyalty['current']
                                    . ' points, ' . LoyaltyService::tierLabel($loyalty['tier']) . ' tier',
                            ],
                        ) ?>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header text-body-secondary">Your loyalty snapshot</h6>
                            </li>
                            <?php foreach (
                                [
                                    'Current points' => Yii::$app->formatter->asInteger($loyalty['current']) . ' pts',
                                    'Lifetime earned' => Yii::$app->formatter->asInteger($loyalty['lifetimeEarned']) . ' pts',
                                    'Lifetime redeemed' => Yii::$app->formatter->asInteger($loyalty['lifetimeRedeemed']) . ' pts',
                                    'Tier' => LoyaltyService::tierLabel($loyalty['tier']),
                                ] as $label => $value
                            ): ?>
                                <li class="d-flex gap-4 px-3 py-1 mb-1 justify-content-between border-bottom border-primary-subtle small">
                                    <span class="text-body-secondary"><?= Html::encode($label) ?></span>
                                    <span class="fw-semibold"><?= Html::encode($value) ?></span>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><?= Html::a('Open My Rewards', ['/loyalty/index'], ['class' => 'dropdown-item cafebara-notification-item']) ?></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('notification.index')): ?>
                    <li class="nav-item">
                        <?= $this->render('_notifications_link', ['class' => 'btn btn-outline-primary']) ?>
                    </li>
                <?php endif ?>
                <li class="nav-item">
                    <?= $this->render('_cart_button') ?>
                </li>
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
    </div>
</nav>