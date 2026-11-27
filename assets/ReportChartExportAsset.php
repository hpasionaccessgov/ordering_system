<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class ReportChartExportAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/report-export.js',
    ];
    public $depends = [
        ChartJsAsset::class,
    ];
}
