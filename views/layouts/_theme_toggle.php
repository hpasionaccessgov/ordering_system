<?php

declare(strict_types=1);

use yii\helpers\Html;

echo Html::button(
    Html::tag('i', '', [
        'class' => 'bi bi-moon-stars-fill',
        'data-theme-icon' => true,
        'aria-hidden' => 'true',
    ]),
    ['class' => 'btn cafebara-theme-toggle', 'aria-label' => 'Switch to dark mode', 'id' => 'theme-toggle']
);
