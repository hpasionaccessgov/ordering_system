<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class BootstrapIconsAsset extends AssetBundle
{
    /** Vendor-only Bootstrap Icons (copied from npm dist; no node_modules at runtime). */
    public $sourcePath = '@app/vendor/asset-libs/bootstrap-icons/font';

    public $css = [
        'bootstrap-icons.css',
    ];
}
