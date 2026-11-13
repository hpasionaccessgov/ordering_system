<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class BootstrapIconsAsset extends AssetBundle
{
    public $sourcePath = '@app/node_modules/bootstrap-icons/font';

    public $css = [
        'bootstrap-icons.css',
    ];
}
