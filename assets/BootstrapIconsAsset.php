<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class BootstrapIconsAsset extends AssetBundle
{
    /** Installed by Composer via asset-packagist. */
    public $sourcePath = '@app/vendor/npm-asset/bootstrap-icons/font';

    public $css = [
        'bootstrap-icons.css',
    ];
}
