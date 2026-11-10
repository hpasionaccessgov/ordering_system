<?php

/** @var app\models\RegisterForm $model */


use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;


$this->title = 'Create account to Ordering System';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Create an account to access your application.';
$this->params['meta_keywords'] = 'yii, yii2, register, sign up, create account';
$this->params['meta_robots'] = 'noindex, nofollow';
$htmlIcon = <<<HTML
{label}<div class="input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label fw-semibold small'];
?>
<div class="site-registration d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 overflow-hidden login-split-card">
        <div class="row g-0">

            <!-- Brand panel -->
            <div class="col-md-5 d-none d-md-flex login-brand-panel text-white">
                <div class="d-flex flex-column justify-content-between p-4 p-lg-5 w-100">
                    <div>
                        <?= Html::img(
                            Yii::getAlias('@web/images/yii3_full_white_for_dark.svg'),
                            [
                                'alt' => 'Yii Framework',
                                'class' => 'mb-4',
                                'height' => 40,
                            ],
                        ) ?>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-3 login-brand-title">
                            Welcome<br>to Ordering System
                        </h2>
                        <p class="opacity-75 mb-0 login-brand-text">
                            Create an account to access the application
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form panel -->
            <div class="col-md-7">
                <div class="p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <!-- Mobile-only logo -->
                        <div class="d-md-none mb-3">
                            <?= Html::img(
                                Yii::getAlias('@web/images/yii3_full_black_for_light.svg'),
                                [
                                    'alt' => 'Yii Framework',
                                    'class' => 'login-mobile-logo',
                                    'height' => 36,
                                ],
                            ) ?>
                        </div>
                        <h1 class="h3 fw-semibold mb-1"><?= Html::encode($this->title) ?></h1>
                        <p class="text-muted small mb-0">Create your account to access</p>
                    </div>

                    <?php $form = ActiveForm::begin(
                        [
                            'id' => 'registration-form',
                            'options' => ['autocomplete' => 'off'],
                            'fieldConfig' => ['labelOptions' => $labelOptions],
                        ],
                    ) ?>

                    <?= $form->field($model, 'username', ['template' => sprintf($htmlIcon, '&#128100;')])->textInput(['autofocus' => true, 'placeholder' => 'Enter your username']) ?>
                    <?= $form->field($model, 'email', ['template' => sprintf($htmlIcon, '&#9993;')])->textInput(['placeholder' => 'Enter your email address']) ?>
                    <?= $form->field($model, 'fullName', ['template' => sprintf($htmlIcon, '&#128100;')])->textInput(['placeholder' => 'Enter your full name']) ?>
                    <?= $form->field($model, 'password', ['template' => sprintf($htmlIcon, '&#128274;')])->passwordInput(['placeholder' => 'Create a password']) ?>
                    <?= $form->field($model, 'passwordConfirm', ['template' => sprintf($htmlIcon, '&#128274;')])->passwordInput(['placeholder' => 'Confirm your password']) ?>

                    <div class="d-grid mb-3">
                        <?= Html::submitButton(
                            'Create account',
                            [
                                'class' => 'btn btn-primary fw-semibold',
                                'name' => 'registration-button',
                            ],
                        ) ?>
                    </div>

                    <div class="text-center">
                        <p class="small text-muted mb-0">
                            Already have an account?
                            <?= Html::a('Log in', ['site/login'], ['class' => 'fw-semibold']) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>