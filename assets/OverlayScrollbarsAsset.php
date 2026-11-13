<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class OverlayScrollbarsAsset extends AssetBundle
{
    public $sourcePath = '@app/node_modules/overlayscrollbars';

    public $css = [
        'styles/overlayscrollbars.css',
    ];

    public $js = [
        'browser/overlayscrollbars.browser.es6.min.js',
    ];
}
