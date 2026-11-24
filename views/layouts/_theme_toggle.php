<?php

declare(strict_types=1);

use app\widgets\ToolTip;
use yii\helpers\Html;

echo Html::button(
    Html::tag('i', '', [
        'class' => 'bi bi-moon-stars-fill',
        'data-theme-icon' => true,
        'aria-hidden' => 'true',
    ]),
    ToolTip::options('Switch to dark mode', [
        'id' => 'theme-toggle',
        'class' => 'btn cafebara-theme-toggle',
        'type' => 'button',
        'aria-label' => 'Switch to dark mode',
    ]),
);