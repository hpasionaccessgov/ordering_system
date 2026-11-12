<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class AuthAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/site.css',
    ];

    public $depends = [
        AdminLteAsset::class,
        CafebaraThemeAsset::class,
    ];
}
