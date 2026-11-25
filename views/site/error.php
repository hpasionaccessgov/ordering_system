<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;
use yii\web\HttpException;

$this->title = $name;
$statusCode = $exception instanceof HttpException ? $exception->statusCode : 500;
?>
<div class="d-flex align-items-center justify-content-center text-center site-error">
    <div class="mx-auto site-error-content">
        <h1 class="mb-0 fw-bold text-body-secondary display-1"><?= Html::encode($statusCode) ?></h1>

        <h2 class="mb-3 fw-semibold display-6"><?= Html::encode($message) ?></h2>

        <p class="mb-4 text-body-secondary">
            The above error occurred while the Web server was processing your request.
            Please contact us if you think this is a server error. Thank you.
        </p>

        <?= Html::a(
            'Go to Homepage',
            Yii::$app->homeUrl,
            ['class' => 'btn btn-primary btn-lg'],
        ) ?>
    </div>
</div>
