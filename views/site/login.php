<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Sign in to Cafébara';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Log in to access your application account.';
$this->params['meta_keywords'] = 'yii, yii2, login, sign in, authentication';
$this->params['meta_robots'] = 'noindex, nofollow';
$htmlIcon = <<<HTML
{label}<div class="input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label fw-semibold small'];
?>
<div class="d-flex py-5 align-items-center justify-content-center site-login">
    <div class="card border-0 overflow-hidden login-split-card">
        <div class="row g-0">

            <!-- Brand panel -->
            <div class="col-md-5 d-none d-md-flex text-white login-brand-panel">
                <div class="d-flex flex-column p-4 p-lg-5 justify-content-between w-100">
                    <div>
                        <?= Html::img(
                            Yii::getAlias('@web/Cafebara_logo.png'),
                            [
                                'alt' => 'Cafébara',
                                'class' => 'login-brand-logo mb-4',
                            ],
                        ) ?>
                    </div>
                    <div>
                        <h2 class="mb-3 fw-bold login-brand-title">
                            Welcome<br>to Cafébara
                        </h2>
                        <p class="mb-0 opacity-75 login-brand-text">
                            Log in to access the application
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form panel -->
            <div class="col-md-7">
                <div class="p-4 p-lg-5">
                    <div class="mb-4 text-center">
                        <!-- Mobile-only logo -->
                        <div class="d-md-none mb-3">
                            <?= Html::img(
                                Yii::getAlias('@web/Cafebara_logo.png'),
                                [
                                    'alt' => 'Cafébara',
                                    'class' => 'login-mobile-logo',
                                ],
                            ) ?>
                        </div>
                        <h1 class="mb-1 fw-bold h3"><?= Html::encode($this->title) ?></h1>
                        <p class="text-body-secondary small">Enter your credentials to access</p>
                    </div>

                    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                    <div class="mb-3">
                        <?= $form->field($model, 'username', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, '<i class="nav-icon bi bi-people "></i>'),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'Username or email',
                                'autofocus' => true,
                            ],
                        ])->textInput()->label('Username or email', $labelOptions) ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'password', [
                            'options' => ['class' => 'mb-0'],
                            'template' => sprintf($htmlIcon, '<i class="nav-icon bi bi-lock"></i>'),
                            'inputOptions' => [
                                'class' => 'form-control',
                                'placeholder' => 'Password',
                            ],
                        ])->passwordInput()->label('Your Password', $labelOptions) ?>
                    </div>

                    <div class="mb-4">
                        <?= $form->field($model, 'rememberMe')->checkbox() ?>
                    </div>

                    <div class="d-grid">
                        <?= Html::submitButton(
                            'Login',
                            [
                                'class' => 'btn login-btn btn-lg rounded-3 text-white',
                                'name' => 'login-button',
                            ],
                        ) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                    <div class="mt-3 text-body-secondary text-center small">
                        Don't have account yet? <?= Html::a('Register Here', ['site/register']) ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
