<?php

use app\models\ListingFilters;
use yii\helpers\Html;

/** @var ListingFilters $filters */
/** @var bool $showAudit */
/** @var bool $showDates */
/** @var bool $showAmount */
/** @var string $amountLabel */

$value = static fn($value): string => is_scalar($value) ? (string) $value : '';
?>
<?= Html::errorSummary($filters, ['class' => 'alert alert-danger mt-2']) ?>
<details class="mt-2"<?= $filters->hasErrors() || array_filter($filters->getAttributes(), static fn($value): bool => $value !== '' && $value !== null) !== [] ? ' open' : '' ?>>
    <summary>More filters</summary>
    <div class="row g-2 mt-1">
        <?php if ($showAmount): ?>
            <?php foreach (['min_amount' => 'Minimum', 'max_amount' => 'Maximum'] as $field => $label): ?>
                <div class="col-12 col-md-3">
                    <?= Html::label($label . ' ' . $amountLabel, $field, ['class' => 'form-label']) ?>
                    <?= Html::input('number', $field, $value($filters->$field), [
                        'id' => $field, 'class' => 'form-control', 'min' => 0, 'step' => '0.01',
                    ]) ?>
                </div>
            <?php endforeach ?>
        <?php endif ?>
        <?php if ($showAudit): ?>
            <?php foreach (['created_by_name' => 'Created by', 'updated_by_name' => 'Updated by'] as $field => $label): ?>
                <div class="col-12 col-md-3">
                    <?= Html::label($label . ' username', $field, ['class' => 'form-label']) ?>
                    <?= Html::input('search', $field, $value($filters->$field), ['id' => $field, 'class' => 'form-control']) ?>
                </div>
            <?php endforeach ?>
        <?php endif ?>
        <?php if ($showDates): ?>
            <?php foreach (['created_from' => 'Created from', 'created_to' => 'Created to', 'updated_from' => 'Updated from', 'updated_to' => 'Updated to'] as $field => $label): ?>
                <div class="col-12 col-md-3">
                    <?= Html::label($label, $field, ['class' => 'form-label']) ?>
                    <?= Html::input('date', $field, $value($filters->$field), ['id' => $field, 'class' => 'form-control']) ?>
                </div>
            <?php endforeach ?>
        <?php endif ?>
    </div>
</details>
