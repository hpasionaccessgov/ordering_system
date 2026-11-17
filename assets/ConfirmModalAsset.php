<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

final class ConfirmModalAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $baseUrl = '@web';

    public $js = [
        'js/confirm-modal.js',
    ];
}
