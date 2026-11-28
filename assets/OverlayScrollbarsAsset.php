<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class OverlayScrollbarsAsset extends AssetBundle
{
    /** Installed by Composer via asset-packagist. */
    public $sourcePath = '@app/vendor/npm-asset/overlayscrollbars';

    public $css = [
        'styles/overlayscrollbars.css',
    ];

    public $js = [
        'browser/overlayscrollbars.browser.es6.min.js',
    ];
}
