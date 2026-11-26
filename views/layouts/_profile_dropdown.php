<?php

declare(strict_types=1);

use app\services\Navigation;
use yii\helpers\Html;

/** @var string $class */

$class = $class ?? 'btn btn-outline-primary';
$username = Yii::$app->user->identity?->username ?? 'Account';
?>
<div class="dropdown">
    <?= Html::button(
        Html::tag('i', '', ['class' => 'bi bi-person-circle', 'aria-hidden' => 'true'])
            . Html::tag('span', Html::encode($username), ['class' => 'cafebara-btn-label d-none d-md-inline fw-bold']),
        [
            'class' => $class . ' dropdown-toggle cafebara-icon-btn',
            'type' => 'button',
            'data-bs-toggle' => 'dropdown',
            'data-bs-auto-close' => 'outside',
            'aria-haspopup' => 'true',
            'aria-expanded' => 'false',
            'aria-label' => 'Account: ' . $username,
        ],
    ) ?>
    <ul class="dropdown-menu dropdown-menu-end cafebara-account-menu cafebara-header-menu" data-bs-popper="static">
        <li>
            <div class="d-flex gap-2 dropdown-header align-items-center">
                <span class="fs-6 text-primary bi bi-person-circle" aria-hidden="true"></span>
                <span class="text-truncate">
                    <span class="fw-bold text-primary"><?= Html::encode($username) ?></span>
                    <span class="d-block text-body-secondary small">Your account</span>
                </span>
            </div>
        </li>
        <li>
            <hr class="dropdown-divider">
        </li>
        <li>
            <?= Html::a(
                '<i class="bi bi-person me-2" aria-hidden="true"></i>Profile',
                Navigation::url(Navigation::PROFILE),
                ['class' => 'dropdown-item cafebara-account-menu-item', 'encode' => false],
            ) ?>
        </li>
        <li>
            <?= Html::beginForm(Navigation::url(Navigation::LOGOUT), 'POST', ['class' => 'm-0']) ?>
            <?= Html::submitButton(
                '<i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Sign out',
                ['class' => 'dropdown-item cafebara-account-menu-item', 'encode' => false],
            ) ?>
            <?= Html::endForm() ?>
        </li>
    </ul>
</div>