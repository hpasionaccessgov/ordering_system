<?php

declare(strict_types=1);

namespace app\services;

use Yii;

/**
 * One formatting path for report tables and every export format.
 */
final class ReportFormat
{
    private const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * @param array<string, mixed> $row
     * @param array{attribute: string, type?: string} $column
     */
    public static function value(array $row, array $column): string
    {
        $value = $row[$column['attribute']] ?? '';

        return match ($column['type'] ?? 'text') {
            'money' => (string) Yii::$app->formatter->asCurrency($value),
            'number' => (string) Yii::$app->formatter->asInteger($value),
            'decimal' => (string) Yii::$app->formatter->asDecimal($value, 2),
            'date' => is_numeric($value) && (int) $value > 0
                ? (string) Yii::$app->formatter->asDate((int) $value, 'php:Y-m-d')
                : (string) $value,
            'status' => self::STATUSES[(string) $value] ?? (string) $value,
            default => (string) $value,
        };
    }
}
