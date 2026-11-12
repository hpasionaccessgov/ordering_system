<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AuthAsset;
use yii\helpers\Html;

AuthAsset::register($this);

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
<body class="bg-body-tertiary d-flex flex-column min-vh-100">
<?php $this->beginBody() ?>

<?= $this->render('_auth_header') ?>

<main class="auth-main d-flex align-items-center" role="main">
    <div class="container py-4">
        <?= $this->render('_flash_messages') ?>
        <?= $content ?>
    </div>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>