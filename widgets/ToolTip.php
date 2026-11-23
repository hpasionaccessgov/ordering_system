<?php

declare(strict_types=1);

namespace app\widgets;

use Yii;
use yii\web\View;

/**
 * Bootstrap 5 tooltip attributes for any element via ToolTip::options().
 */
final class ToolTip
{
    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public static function options(string $text, array $options = [], string $placement = 'top'): array
    {
        Yii::$app->view->registerJs(
            <<<'JS'
if (window.bootstrap && window.bootstrap.Tooltip) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
        new window.bootstrap.Tooltip(element);
    });
}
JS,
            View::POS_READY,
            'cafebara-tooltip-init',
        );

        return array_merge($options, [
            'data-bs-toggle' => 'tooltip',
            'data-bs-placement' => $placement,
            'title' => $text,
        ]);
    }
}
