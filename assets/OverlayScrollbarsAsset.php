<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class OverlayScrollbarsAsset extends AssetBundle
{
    /** Vendor-only OverlayScrollbars (no node_modules at runtime). */
    public $sourcePath = '@app/vendor/asset-libs/overlayscrollbars';

    public $css = [
        'styles/overlayscrollbars.css',
    ];

    public $js = [
        'browser/overlayscrollbars.browser.es6.min.js',
    ];
}
