<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class BootstrapJsAsset extends AssetBundle
{
    public $sourcePath = '@app/node_modules/bootstrap/dist/js';

    public $js = [
        'bootstrap.bundle.min.js',
    ];
}
