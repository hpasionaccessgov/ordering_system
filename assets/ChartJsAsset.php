<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class ChartJsAsset extends AssetBundle
{
    /** Installed by Composer via asset-packagist. */
    public $sourcePath = '@app/vendor/npm-asset/chart.js/dist';

    public $js = [
        'chart.umd.min.js',
    ];
}
