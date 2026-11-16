<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\User;
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
            Review accounts and update their personal details or access.
        </p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            'full_name',
            'email:email',
            [
                'label' => 'Actions',
                'format' => 'raw',
                'value' => static function (User $user): string {
                    if (!Yii::$app->user->can('admin.access')) {
                        return '';
                    }

                    return Html::a('Edit', ['/admin-user/update', 'id' => $user->id], [
                        'class' => 'btn btn-outline-primary',
                        'aria-label' => 'Edit personal data for ' . $user->username,
                    ]);
                },
            ],
        ],
    ]) ?>
</div>
