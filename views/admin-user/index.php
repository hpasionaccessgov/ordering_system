<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\User;
use app\widgets\AdminButton;
use app\widgets\AdminGridView;
use yii\helpers\Html;

$this->title = 'User list';
?>

<div class="card">
    <div class="card-header">
        <h1 class="h5 mb-0">User list</h1>
    </div>
    <div class="card-body border-bottom">
        <p class="text-body-secondary mb-0">
            Review account details and allow or block access.
        </p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            'email:email',
            [
                'label' => 'Status',
                'format' => 'raw',
                'value' => static function (User $user): string {
                    $isActive = $user->status === User::STATUS_ACTIVE;
                    return Html::beginForm(
                        ['/admin-user/update-status', 'id' => $user->id],
                        'post',
                        ['class' => 'd-flex gap-2 align-items-center'],
                    )
                        . Html::dropDownList('status', $user->status, [
                            User::STATUS_ACTIVE => 'Allowed',
                            User::STATUS_DISABLED => 'Blocked',
                        ], ['class' => 'form-select form-select-sm', 'aria-label' => 'Status for ' . $user->username])
                        . AdminButton::submit('Save', 'primary', ['class' => 'btn-sm'])
                        . Html::endForm();
                },
            ],
        ],
    ]) ?>
</div>
