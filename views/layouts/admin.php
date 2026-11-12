<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AdminLteAsset;
use app\assets\CafebaraThemeAsset;
use app\widgets\Alert;
use yii\helpers\Html;

AdminLteAsset::register($this);
CafebaraThemeAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag([
    'name' => 'viewport',
    'content' => 'width=device-width, initial-scale=1',
]);
?>
<?php $this->beginPage() ?>
<!doctype html>
<html lang="<?= Html::encode(Yii::$app->language) ?>" data-bs-theme="light">
<head>
    <title><?= Html::encode($this->title ?: Yii::$app->name) ?></title>
    <?php $this->head() ?>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<?php $this->beginBody() ?>

<div class="app-wrapper">
    <?= $this->render('_admin_header') ?>
    <?= $this->render('_admin_sidebar') ?>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <h1 class="mb-0 fs-3"><?= Html::encode($this->title) ?></h1>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <?= Alert::widget() ?>
                <?= $content ?>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <strong>&copy; <?= date('Y') ?> <?= Html::encode(Yii::$app->name) ?></strong>
    </footer>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>