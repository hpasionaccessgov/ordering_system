<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

class ColorModeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/color-mode.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}