<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

class AdminLteAsset extends AssetBundle
{
    public $sourcePath = "@app/node_modules/admin-lte/dist";

    public $css = [
        'css/adminlte.min.css',
    ];

    public $js = [
        'js/adminlte.min.js',
    ];

    public $depends = [
        YiiAsset::class,
        BootstrapJsAsset::class,
        BootstrapIconsAsset::class,
        OverlayScrollbarsAsset::class,
    ];
}
