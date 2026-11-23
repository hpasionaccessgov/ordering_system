<?php

/** @var app\models\RegisterForm $model */


use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;


$this->title = 'Create your Cafébara account';
$this->params['breadcrumbs'][] = $this->title;
$this->params['meta_description'] = 'Create an account to access your application.';
$this->params['meta_keywords'] = 'yii, yii2, register, sign up, create account';
$this->params['meta_robots'] = 'noindex, nofollow';
$htmlIcon = <<<HTML
{label}<div class="input-group"><span class="input-group-text" aria-hidden="true">%s</span>{input}</div>{error}{hint}
HTML;
$labelOptions = ['class' => 'form-label fw-semibold small'];
?>
<div class="d-flex py-5 align-items-center justify-content-center site-registration">
    <div class="card border-0 overflow-hidden login-split-card">
        <div class="row g-0">

            <!-- Brand panel -->
            <div class="col-md-5 d-none d-md-flex text-white login-brand-panel">
                <div class="d-flex flex-column p-4 justify-content-between w-100">
                    <div>
                        <?= Html::img(
                            Yii::getAlias('@web/Cafebara_logo.png'),
                            [
                                'alt' => 'Cafébara',
                                'class' => 'login-brand-logo',
                            ],
                        ) ?>
                    </div>
                    <div>
                        <h2 class="mb-3 fw-bold login-brand-title">
                            Welcome<br>to Cafébara
                        </h2>
                        <p class="mb-0 opacity-75 login-brand-text">
                            Create an account to access the application
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
                        <h1 class="mb-1 fw-semibold h3"><?= Html::encode($this->title) ?></h1>
                        <p class="mb-0 text-muted small">Create your account to access</p>
                    </div>

                    <?php $form = ActiveForm::begin(
                        [
                            'id' => 'registration-form',
                            'options' => ['autocomplete' => 'off'],
                            'fieldConfig' => ['labelOptions' => $labelOptions],
                        ],
                    )
                    ?>

                    <?= $form->errorSummary($model) ?>

                    <?= $form->field($model, 'username', ['template' => sprintf($htmlIcon, '<i class="nav-icon bi bi-people "></i>')])->textInput(['autofocus' => true, 'placeholder' => 'Enter your username']) ?>
                    <?= $form->field($model, 'email', ['template' => sprintf($htmlIcon, '<i class="nav-icon bi bi-envelope"></i>')])->textInput(['placeholder' => 'Enter your email address']) ?>
                    <?= $form->field($model, 'fullName', ['template' => sprintf($htmlIcon, '<i class="nav-icon bi bi-person"></i>')])->textInput(['placeholder' => 'Enter your full name']) ?>
                    <?= $form->field($model, 'password', ['template' => sprintf($htmlIcon, '<i class="nav-icon bi bi-lock"></i>')])->passwordInput(['placeholder' => 'Create a password']) ?>
                    <?= $form->field($model, 'passwordConfirm', ['template' => sprintf($htmlIcon, '<i class="nav-icon bi bi-lock"></i>')])->passwordInput(['placeholder' => 'Confirm your password']) ?>

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
                        <p class="mb-0 text-muted small">
                            Already have an account?
                            <?= Html::a('Log in', ['site/login'], ['class' => 'fw-semibold']) ?>
                        </p>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>