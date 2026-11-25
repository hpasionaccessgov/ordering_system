<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class BootstrapJsAsset extends AssetBundle
{
    /** Vendor-only Bootstrap JS (no node_modules). */
    public $sourcePath = '@app/vendor/twbs/bootstrap/dist/js';

    public $js = [
        'bootstrap.bundle.min.js',
    ];
}
