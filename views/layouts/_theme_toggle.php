<?php

declare(strict_types=1);

use yii\helpers\Html;

echo Html::button(
    Html::tag('i', '', [
        'class' => 'bi bi-moon-stars-fill',
        'data-theme-icon' => true,
        'aria-hidden' => 'true',
    ]),
    [
        'id' => 'theme-toggle',
        'class' => 'btn cafebara-theme-toggle',
        'type' => 'button',
        'aria-label' => 'Switch to dark mode',
        'title' => 'Switch to dark mode',
    ],
);