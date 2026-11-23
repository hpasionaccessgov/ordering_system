<?php

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\rbac\Role[] $roles */
/** @var string $search */
/** @var \app\models\UserSearch $searchModel */

use app\models\User;
use app\widgets\AdminGridView;
use app\widgets\ToolTip;
use yii\helpers\Html;

$this->title = 'User list';
$roleOptions = ['' => 'Select role'];
foreach ($roles as $role) {
    $roleOptions[$role->name] = $role->description !== ''
        ? $role->description . ' (' . $role->name . ')'
        : ucfirst($role->name);
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Administration</p>
        <h1 class="h3 mb-0">User list</h1>
    </div>
    <?php if (Yii::$app->user->can('user.create')) : ?>
        <?= Html::a('Create account', ['/admin-user/create'], ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<?= $this->render('_search', ['search' => $search, 'model' => $searchModel, 'roles' => $roles]) ?>

<section class="card admin-panel">
    <div class="card-header">
        <p class="text-body-secondary mb-0">Review account details, roles, and access.</p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'username',
                'format' => 'raw',
                'value' => static function (User $user): string {
                    if (!Yii::$app->user->can('user.view')) {
                        return Html::encode($user->username);
                    }

                    return Html::a(
                        Html::encode($user->username),
                        ['/admin-user/view', 'id' => $user->id],
                    );
                },
            ],
            'full_name',
            'email:email',
            'created_at:datetime',
            'updated_at:datetime',
            [
                'attribute' => 'created_by',
                'label' => 'Created by',
                'value' => static fn(User $user): string =>
                $user->createdBy?->username ?? '—',
            ],
            [
                'attribute' => 'updated_by',
                'label' => 'Updated by',
                'value' => static fn(User $user): string =>
                $user->updatedBy?->username ?? '—',
            ],
            [
                'label' => 'Role',
                'format' => 'raw',
                'value' => static function (User $user) use ($roleOptions): string {
                    $auth = Yii::$app->authManager;
                    $assigned = $auth->getRolesByUser($user->id);
                    $selected = count($assigned) === 1 ? array_key_first($assigned) : '';

                    if (!Yii::$app->user->can('user.assignRole')) {
                        return Html::encode(implode(', ', array_keys($assigned)) ?: 'No role assigned');
                    }

                    if ((string) $user->id === (string) Yii::$app->user->id) {
                        return '<span class="text-body-secondary">Current account</span>';
                    }

                    return Html::beginForm(
                        ['/admin-user/assign-role', 'id' => $user->id],
                        'post',
                        ['class' => 'd-flex flex-wrap gap-2 role-assignment-form'],
                    )
                        . Html::dropDownList('role', $selected, $roleOptions, [
                            'class' => 'form-select form-select-sm',
                            'required' => true,
                            'aria-label' => 'Role for ' . $user->username,
                        ])
                        . Html::submitButton('Save', ['class' => 'btn btn-sm btn-primary'])
                        . Html::endForm();
                },
            ],
            [
                'label' => 'Actions',
                'format' => 'raw',
                'value' => static function (User $user): string {
                    $actions = [];
                    if (Yii::$app->user->can('user.view')) {
                        $actions[] = Html::a('View', ['/admin-user/view', 'id' => $user->id], ToolTip::options(
                            'View account details for ' . $user->username,
                            [
                                'class' => 'btn btn-sm btn-outline-secondary',
                                'aria-label' => 'View account details for ' . $user->username,
                            ],
                        ));
                    }

                    if (
                        Yii::$app->user->can('user.update')
                        && Yii::$app->user->can('user.view')
                    ) {
                        $actions[] = Html::a('Edit', ['/admin-user/update', 'id' => $user->id], ToolTip::options(
                            'Edit personal data for ' . $user->username,
                            [
                                'class' => 'btn btn-sm btn-outline-primary',
                                'aria-label' => 'Edit personal data for ' . $user->username,
                            ],
                        ));
                    }

                    if (
                        Yii::$app->user->can('user.delete')
                        && (string) $user->id !== (string) Yii::$app->user->id
                    ) {
                        $actions[] = Html::beginForm(
                            ['/admin-user/delete', 'id' => $user->id],
                            'post',
                            ['class' => 'd-inline'],
                        )
                            . Html::submitButton('Delete', ToolTip::options(
                                'Delete ' . $user->username,
                                [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'data-confirm-modal' => 'Permanently delete this account? Accounts with order history cannot be deleted.',
                                ],
                            ))
                            . Html::endForm();
                    }

                    return implode(' ', $actions);
                },
            ],
        ],
    ]) ?>
</section>