<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class ChartJsAsset extends AssetBundle
{
    public $sourcePath = '@app/node_modules/chart.js/dist';

    public $js = [
        'chart.umd.js',
    ];
}
