<?php

use app\models\ProfileForm;
use app\models\User;
use app\widgets\AdminActiveForm;
use yii\helpers\Html;

/** @var User $user */
/** @var ProfileForm $model */
/** @var array{current: int, lifetimeEarned: int, lifetimeRedeemed: int, tier: string} $loyalty */

$this->title = 'My Profile';
?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <?php if (Yii::$app->user->can('loyalty.view')) : ?>
            <section class="card admin-panel mb-4">
                <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Loyalty snapshot</h2>
                    <?= Html::a('My rewards', ['/loyalty/index'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <p class="mb-1 text-body-secondary small">Current points</p>
                            <p class="h4 mb-0"><?= Yii::$app->formatter->asInteger($loyalty['current']) ?></p>
                        </div>
                        <div class="col-6 col-md-3">
                            <p class="mb-1 text-body-secondary small">Lifetime earned</p>
                            <p class="h4 mb-0"><?= Yii::$app->formatter->asInteger($loyalty['lifetimeEarned']) ?></p>
                        </div>
                        <div class="col-6 col-md-3">
                            <p class="mb-1 text-body-secondary small">Lifetime redeemed</p>
                            <p class="h4 mb-0"><?= Yii::$app->formatter->asInteger($loyalty['lifetimeRedeemed']) ?></p>
                        </div>
                        <div class="col-6 col-md-3">
                            <p class="mb-1 text-body-secondary small">Tier</p>
                            <?= $this->render('../shared/_loyalty_badge', ['tier' => $loyalty['tier']]) ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="mb-0 card-title">Profile details</h2>
            </div>

            <div class="card-body">
                <dl class="row mb-4">

                    <dt class="col-sm-3">Full Name</dt>
                    <dd class="col-sm-9"><?= Html::encode($user->full_name) ?></dd>

                    <dt class="col-sm-3">Username</dt>
                    <dd class="col-sm-9"><?= Html::encode($user->username) ?></dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9"><?= Html::encode($user->email) ?></dd>

                </dl>

                <?php $form = AdminActiveForm::begin(['id' => 'profile-form']); ?>

                <?= $form->field($model, 'fullName')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>


                <?= Html::submitButton('Save profile', ['class' => 'btn btn-primary']) ?>

                <?php AdminActiveForm::end(); ?>
            </div>
        </div>

        <div class="mt-4 card">
            <div class="card-header">
                <h2 class="mb-0 card-title">Account actions</h2>
            </div>

            <div class="d-flex flex-wrap gap-2 card-body">
                <?= Html::a('Change password', ['/profile/change-password'], ['class' => 'btn btn-primary']) ?>

                <?= Html::a('Deactivate account', ['/profile/deactivate'], ['class' => 'btn btn-danger']) ?>

                <?= Html::beginForm(['/site/logout'], 'POST', ['class' => 'd-inline']) ?>
                <?= Html::submitButton('Logout', ['class' => 'btn btn-warning']) ?>
                <?= Html::endForm() ?>
            </div>
        </div>
    </div>