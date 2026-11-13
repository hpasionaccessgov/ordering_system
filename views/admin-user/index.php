<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\User;
use app\widgets\AdminButton;
use app\widgets\AdminGridView;
use yii\helpers\Html;

$this->title = 'User roles';
?>

<div class="card">
    <div class="card-header">
        <h1 class="h5 mb-0">User roles</h1>
    </div>
    <div class="card-body border-bottom">
        <p class="text-body-secondary mb-0">
            Assign one role per account. New registrations are always assigned the Customer role.
            You cannot change your own role or demote the last administrator.
        </p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            'email:email',
            [
                'label' => 'Role',
                'format' => 'raw',
                'value' => static function (User $user): string {
                    $roles = Yii::$app->authManager->getRolesByUser($user->id);
                    $selectedRole = count($roles) === 1 ? array_key_first($roles) : '';

                    if ((string) $user->id === (string) Yii::$app->user->id) {
                        return Html::tag(
                            'span',
                            Html::encode($selectedRole === '' ? 'Multiple or unassigned' : ucfirst($selectedRole))
                                . ' (current account)',
                            ['class' => 'text-body-secondary'],
                        );
                    }

                    return Html::beginForm(
                        ['/admin-user/assign-role', 'id' => $user->id],
                        'post',
                        ['class' => 'd-flex gap-2 align-items-center'],
                    )
                        . Html::dropDownList(
                            'role',
                            $selectedRole,
                            [
                                '' => 'Select role',
                                'admin' => 'Admin',
                                'staff' => 'Staff',
                                'customer' => 'Customer',
                            ],
                            [
                                'class' => 'form-select form-select-sm',
                                'aria-label' => 'Role for ' . $user->username,
                                'required' => true,
                            ],
                        )
                        . AdminButton::submit('Save', 'primary', ['class' => 'btn-sm'])
                        . Html::endForm();
                },
            ],
        ],
    ]) ?>
</div>
