<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class ChartJsAsset extends AssetBundle
{
    /** Vendor-only Chart.js (no node_modules at runtime). */
    public $sourcePath = '@app/vendor/asset-libs/chart.js/dist';

    public $js = [
        'chart.umd.min.js',
    ];
}
