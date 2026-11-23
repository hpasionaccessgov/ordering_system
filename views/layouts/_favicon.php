<?php

declare(strict_types=1);

/** @var yii\web\View $this */

$this->registerLinkTag(
    [
        'rel' => 'icon',
        'type' => 'image/png',
        'href' => Yii::getAlias('@web/Cafebara_logo.png'),
    ],
);
