<?php

declare(strict_types=1);

use yii\helpers\Html;

$alertClasses = [
    'success' => 'success',
    'error' => 'danger',
    'danger' => 'danger',
    'warning' => 'warning',
    'info' => 'info',
];

foreach (Yii::$app->session->getAllFlashes(true) as $type => $messages):
    $alertClass = $alertClasses[$type] ?? 'info';
    foreach ((array) $messages as $message):
?>
        <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show" role="alert">
            <?= Html::encode((string) $message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
<?php
    endforeach;
endforeach;