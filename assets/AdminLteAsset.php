<?php

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

class AdminLteAsset extends AssetBundle
{
    /**
     * AdminLTE dist (v4.x). Vendor-only; no node_modules fallback.
     * Supported drop locations:
     * - @app/vendor/almasaeed2010/adminlte/dist
     * - @app/almasaeed2010/adminlte/dist
     */
    public $sourcePath = '@app/vendor/almasaeed2010/adminlte/dist';

    public $css = [
        'css/adminlte.min.css',
    ];

    public $js = [
        'js/adminlte.min.js',
    ];

    public $depends = [
        ColorModeAsset::class,
        YiiAsset::class,
        BootstrapJsAsset::class,
        BootstrapIconsAsset::class,
        OverlayScrollbarsAsset::class,
    ];

    public function init(): void
    {
        foreach ([
            '@app/vendor/almasaeed2010/adminlte/dist',
            '@app/almasaeed2010/adminlte/dist',
        ] as $alias) {
            $path = \Yii::getAlias($alias, false);
            if ($path !== false && is_dir($path)) {
                $this->sourcePath = $alias;
                break;
            }
        }

        parent::init();
    }
}
