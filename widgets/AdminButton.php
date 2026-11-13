<?php

declare(strict_types=1);

namespace app\widgets;

use yii\base\InvalidArgumentException;
use yii\helpers\Html;

final class AdminButton
{
    private const VARIANTS = [
        'link',
        'primary',
        'secondary',
        'outline-primary',
        'outline-secondary',
        'outline-success',
        'outline-danger',
        'outline-warning',
        'outline-info',
        'outline-light',
        'outline-dark',
        'cafebara-light',
        'success',
        'danger',
        'warning',
        'light',
    ];

    public static function link(
        string $label,
        string|array $url,
        string $variant = 'primary',
        array $options = [],
    ): string {
        return Html::a(Html::encode($label), $url, self::withVariant($variant, $options));
    }

    public static function submit(
        string $label,
        string $variant = 'primary',
        array $options = [],
    ): string {
        return Html::submitButton(Html::encode($label), self::withVariant($variant, $options));
    }

    public static function button(
        string $label,
        string $variant = 'secondary',
        array $options = [],
    ): string {
        $options['type'] ??= 'button';

        return Html::button(Html::encode($label), self::withVariant($variant, $options));
    }

    private static function withVariant(string $variant, array $options): array
    {
        if (!in_array($variant, self::VARIANTS, true)) {
            throw new InvalidArgumentException("Unsupported button variant: {$variant}");
        }

        Html::addCssClass($options, ['btn', 'btn-' . $variant]);

        return $options;
    }
}