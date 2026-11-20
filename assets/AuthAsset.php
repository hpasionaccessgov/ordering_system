<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class AuthAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/cafebara-theme.css',
    ];

    public $depends = [
        ColorModeAsset::class,
        AdminLteAsset::class,
        CafebaraThemeAsset::class,
    ];
}
