<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\ApplicationSettings;
use app\models\Category;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

final class CategoryController extends ApiController
{
    public bool $authRequired = false;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['index'], 'allow' => true, 'roles' => ['?', '@']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return ['index' => ['GET']];
    }

    public function actionIndex(): array
    {
        $provider = new ActiveDataProvider([
            'query' => Category::find()
                ->where(['status' => Category::STATUS_ACTIVE])
                ->orderBy(['name' => SORT_ASC]),
            'pagination' => ApplicationSettings::pagination(),
        ]);

        $categories = array_map(
            static fn(Category $category): array => $category->toArray(['id', 'name', 'description']),
            $provider->getModels(),
        );

        return $this->ok($categories, ['pagination' => $this->paginationMeta($provider->getPagination())]);
    }
}
