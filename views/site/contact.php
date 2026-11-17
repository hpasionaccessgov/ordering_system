<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\ContactForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\captcha\Captcha;

$this->title = 'Contact Cafébara';
$this->params['meta_description'] = 'Contact Cafébara with questions about the menu, your account, or an order.';
$this->params['meta_keywords'] = 'Cafébara, contact, order help, cafe';
$labelOptions = ['class' => 'form-label fw-semibold'];
?>

<div class="container py-4 site-contact">
    <section class="cafebara-contact-hero p-4 p-lg-5 mb-4">
        <p class="cafebara-dashboard-kicker mb-2">We are here to help</p>
        <h1 class="display-6 fw-bold mb-2">Contact Cafébara</h1>
        <p class="lead mb-0">
            Send a note about the menu, your account, or an order.
        </p>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')) : ?>
        <section class="cafebara-contact-form-card p-4 p-lg-5 text-center" role="status">
            <?= Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
                'class' => 'cafebara-about-logo mb-3',
                'alt' => '',
            ]) ?>
            <h2 class="h3 fw-bold">Thanks for reaching out</h2>
            <p class="text-body-secondary mb-4">
                Your message has been received.
            </p>
            <?php if (
                YII_DEBUG
                && Yii::$app->mailer instanceof \yii\mail\BaseMailer
                && Yii::$app->mailer->useFileTransport
) : ?>
                <p class="text-body-secondary small mb-4">
                    Development mode: the email was saved under
                    <code><?= Html::encode(Yii::getAlias(Yii::$app->mailer->fileTransportPath)) ?></code>.
                </p>
            <?php endif; ?>
            <?= Html::a('Send another message', ['contact'], ['class' => 'btn btn-primary']) ?>
        </section>
    <?php else : ?>
        <div class="row g-4 align-items-stretch">
            <div class="col-12 col-lg-4">
                <aside class="cafebara-contact-aside">
                    <?= Html::img(Yii::getAlias('@web/Cafebara_logo.png'), [
                        'class' => 'cafebara-about-logo mb-4',
                        'alt' => '',
                    ]) ?>
                    <h2 class="h4 fw-bold">How can we help?</h2>
                    <p class="text-body-secondary">
                        A little context helps us understand what you need.
                    </p>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex gap-3">
                            <i class="bi bi-receipt text-primary" aria-hidden="true"></i>
                            <span>
                                <strong class="d-block">Order question</strong>
                                <span class="text-body-secondary small">
                                    Include the order number shown on your order page.
                                </span>
                            </span>
                        </li>
                        <li class="d-flex gap-3">
                            <i class="bi bi-person-circle text-primary" aria-hidden="true"></i>
                            <span>
                                <strong class="d-block">Account question</strong>
                                <span class="text-body-secondary small">
                                    Tell us the username associated with your account.
                                </span>
                            </span>
                        </li>
                        <li class="d-flex gap-3">
                            <i class="bi bi-chat-dots text-primary" aria-hidden="true"></i>
                            <span>
                                <strong class="d-block">Something else</strong>
                                <span class="text-body-secondary small">
                                    Choose a clear subject and describe what happened.
                                </span>
                            </span>
                        </li>
                    </ul>
                </aside>
            </div>

            <div class="col-12 col-lg-8">
                <section class="cafebara-contact-form-card p-4 p-lg-5">
                    <h2 class="h4 fw-bold mb-1">Send us a message</h2>
                    <p class="text-body-secondary mb-4">
                        Complete the form below and we will review your message.
                    </p>

                    <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <?= $form->field($model, 'name')->textInput([
                                'placeholder' => 'Your name',
                                'autocomplete' => 'name',
                            ])->label('Name', $labelOptions) ?>
                        </div>
                        <div class="col-sm-6">
                            <?= $form->field($model, 'email')->input('email', [
                                'placeholder' => 'you@example.com',
                                'autocomplete' => 'email',
                            ])->label('Email', $labelOptions) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'subject')->textInput([
                                'placeholder' => 'What is this about?',
                            ])->label('Subject', $labelOptions) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'body')->textarea([
                                'rows' => 6,
                                'placeholder' => 'Share the details that will help us understand your question.',
                            ])->label('Message', $labelOptions) ?>
                        </div>
                        <div class="col-12 d-flex flex-wrap align-items-end gap-3">
                            <?= $form->field($model, 'verifyCode', [
                                'options' => ['class' => 'mb-0'],
                            ])->widget(Captcha::class, [
                                'template' => '<div class="d-flex align-items-center gap-2">{image}{input}</div>',
                            ])->label('Verification code', $labelOptions) ?>
                            <?= Html::submitButton('Send message', [
                                'class' => 'btn btn-primary ms-auto',
                                'name' => 'contact-button',
                            ]) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </section>
            </div>
        </div>
    <?php endif; ?>
</div>
