<?php

use app\services\ReportFormat;
use yii\helpers\Html;

/**
 * Standalone printable document rendered by ReportExportService (no layout / assets).
 *
 * @var string $title
 * @var string $generatedAt
 * @var string $filterLabel
 * @var array<string, string> $summary
 * @var list<array{label: string, attribute: string, type?: string}> $columns
 * @var list<array<string, mixed>> $rows
 */

$plain = static fn(string $value): string => str_replace('₱', 'PHP ', $value);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode($title) ?></title>
    <style>
        @page { margin: 14mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 20px; margin: 0 0 6px; color: #4a2a17; }
        .brand { font-size: 12px; font-weight: bold; letter-spacing: 1px; color: #89431c; }
        .meta { color: #555; margin: 2px 0; }
        .summary, .data { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .summary td { border: 1px solid #d8cdc4; padding: 7px; vertical-align: top; }
        .summary .label { font-size: 8px; text-transform: uppercase; color: #777; }
        .summary .value { font-size: 12px; font-weight: bold; }
        .data th { background: #f2e7dc; border: 1px solid #cfc0b2; padding: 6px; text-align: left; }
        .data td { border: 1px solid #ddd; padding: 5px 6px; }
        .data tr:nth-child(even) td { background: #faf7f4; }
        .data td.number { text-align: right; }
        footer { margin-top: 14px; border-top: 1px solid #ddd; padding-top: 6px; color: #777; font-size: 8px; }
    </style>
</head>
<body>
    <div class="brand">CAFÉBARA</div>
    <h1><?= Html::encode($title) ?></h1>
    <p class="meta">Generated: <?= Html::encode($generatedAt) ?></p>
    <p class="meta">Applied filters: <?= Html::encode($filterLabel) ?></p>

    <table class="summary">
        <tr>
            <?php foreach ($summary as $label => $value) : ?>
                <?php
                $caption = null;
                if (is_array($value)) {
                    $caption = isset($value['caption']) ? (string) $value['caption'] : null;
                    $label = (string) ($value['label'] ?? $label);
                    $value = (string) ($value['value'] ?? '');
                }
                ?>
                <td>
                    <div class="label"><?= Html::encode((string) $label) ?></div>
                    <div class="value"><?= Html::encode($plain((string) $value)) ?></div>
                    <?php if ($caption !== null && $caption !== '') : ?>
                        <div class="meta"><?= Html::encode($caption) ?></div>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <?php foreach ($columns as $column) : ?>
                    <th><?= Html::encode($column['label']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []) : ?>
                <tr><td colspan="<?= count($columns) ?>">No data for the selected filters.</td></tr>
            <?php else : ?>
                <?php foreach ($rows as $row) : ?>
                    <tr>
                        <?php foreach ($columns as $column) : ?>
                            <td class="<?= in_array($column['type'] ?? '', ['number', 'decimal', 'money'], true) ? 'number' : '' ?>">
                                <?= Html::encode($plain(ReportFormat::value($row, $column))) ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <footer>Cafébara — <?= Html::encode($title) ?> — <?= Html::encode($generatedAt) ?></footer>
</body>
</html>
