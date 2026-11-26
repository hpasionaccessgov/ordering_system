<?php

use app\services\Navigation;
use app\models\User;
use app\widgets\AdminGridView;
use app\widgets\ToolTip;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\rbac\Role[] $roles */
/** @var string $search */
/** @var \app\models\UserSearch $searchModel */

$this->title = 'User list';
$roleOptions = ['' => 'Select role'];
foreach ($roles as $role) {
    $roleOptions[$role->name] = $role->description !== ''
        ? $role->description . ' (' . $role->name . ')'
        : ucfirst($role->name);
}
?>

<div class="d-flex flex-wrap gap-3 mb-4 justify-content-between align-items-center">
    <div>
        <p class="mb-1 text-uppercase fw-semibold text-body-secondary small">Administration</p>
        <h1 class="mb-0 h3">User list</h1>
    </div>
    <?php if (Yii::$app->user->can('user.create')) : ?>
        <?= Html::a('Create account', Navigation::url(Navigation::ADMIN_USER_CREATE), ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<?= $this->render('_search', ['search' => $search, 'model' => $searchModel, 'roles' => $roles]) ?>

<section class="card admin-panel">
    <div class="card-header">
        <p class="mb-0 text-body-secondary">Review account details, roles, and access.</p>
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
                        Navigation::url(Navigation::ADMIN_USER_VIEW, ['id' => $user->id]),
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
                        Navigation::url(Navigation::ADMIN_USER_ASSIGN_ROLE, ['id' => $user->id]),
                        'POST',
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
                        $actions[] = Html::a('View', Navigation::url(Navigation::ADMIN_USER_VIEW, ['id' => $user->id]), ['class' => 'btn btn-sm btn-outline-secondary']);
                    }

                    if (
                        Yii::$app->user->can('user.update')
                        && Yii::$app->user->can('user.view')
                    ) {
                        $actions[] = Html::a('Edit', Navigation::url(Navigation::ADMIN_USER_UPDATE, ['id' => $user->id]), ['class' => 'btn btn-sm btn-primary']);
                    }

                    if (
                        Yii::$app->user->can('user.delete')
                        && (string) $user->id !== (string) Yii::$app->user->id
                    ) {
                        $actions[] = Html::beginForm(
                            Navigation::url(Navigation::ADMIN_USER_DELETE, ['id' => $user->id]),
                            'POST',
                            ['class' => 'd-inline'],
                        )
                            . Html::submitButton('Delete', ['class' => 'btn btn-sm btn-outline-danger'])
                            . Html::endForm();
                    }

                    return implode(' ', $actions);
                },
            ],
        ],
    ]) ?>
</section>