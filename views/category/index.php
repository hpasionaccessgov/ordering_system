<?php

use app\models\Category;
use app\widgets\AdminGridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\CategorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $canViewCategories */

$this->title = 'Categories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="text-uppercase small fw-semibold text-body-secondary mb-1">Product catalog</p>
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
    </div>
    <?php if (Yii::$app->user->can('category.create')) : ?>
        <?= Html::a('Create category', ['create'], ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>

<?php if ($canViewCategories) : ?>
<?= $this->render('_search', ['model' => $searchModel]) ?>

<section class="card admin-panel">
    <div class="card-header">
        <p class="text-body-secondary mb-0">Manage product categories and availability.</p>
    </div>
    <?= AdminGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'description:ntext',
            [
                'attribute' => 'status',
                'value' => static fn(Category $category): string =>
                (int) $category->status === Category::STATUS_ACTIVE ? 'Active' : 'Inactive',
            ],
            'created_at:datetime',
            'updated_at:datetime',
            [
                'attribute' => 'created_by',
                'label' => 'Created by',
                'value' => static fn(Category $category): string =>
                $category->createdBy?->username ?? '—',
            ],
            [
                'attribute' => 'updated_by',
                'label' => 'Updated by',
                'value' => static fn(Category $category): string =>
                $category->updatedBy?->username ?? '—',
            ],
            [
                'label' => 'Actions',
                'format' => 'raw',
                'value' => static function (Category $category): string {
                    $actions = [];
                    if (Yii::$app->user->can('category.view')) {
                        $actions[] = Html::a('View', ['view', 'id' => $category->id], [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'aria-label' => 'View category ' . $category->name,
                        ]);
                    }
                    if (Yii::$app->user->can('category.update') && Yii::$app->user->can('category.view')) {
                        $actions[] = Html::a('Edit', ['update', 'id' => $category->id], [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'aria-label' => 'Edit category ' . $category->name,
                        ]);
                    }
                    if (Yii::$app->user->can('category.delete')) {
                        $actions[] = Html::beginForm(
                            ['delete', 'id' => $category->id],
                            'post',
                            ['class' => 'd-inline'],
                        )
                        . Html::submitButton('Delete', [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'aria-label' => 'Delete category ' . $category->name,
                            'data-confirm-modal' => 'Delete this category? Categories assigned to products cannot be deleted.',
                        ])
                        . Html::endForm();
                    }

                    return implode(' ', $actions);
                },
            ],
        ],
    ]) ?>
</section>
<?php else : ?>
    <div class="alert alert-warning" role="alert">You do not have permission to view categories.</div>
<?php endif; ?>