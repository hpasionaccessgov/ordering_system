<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\ApplicationSettings;
use app\models\Favorite;
use app\services\FavoriteService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

final class FavoriteController extends ApiController
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['index'], 'allow' => true, 'roles' => ['favorite.index']],
                ['actions' => ['add'], 'allow' => true, 'roles' => ['favorite.add']],
                ['actions' => ['remove'], 'allow' => true, 'roles' => ['favorite.remove']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
            'add' => ['POST'],
            'remove' => ['DELETE'],
        ];
    }

    public function actionIndex(): array
    {
        $provider = new ActiveDataProvider([
            'query' => Favorite::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->with('product')
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ApplicationSettings::pagination(true),
            'sort' => false,
        ]);

        $favorites = array_map(
            static fn(Favorite $favorite): array => [
                'id' => (int) $favorite->id,
                'product_id' => (int) $favorite->product_id,
                'product' => $favorite->product?->toArray(['id', 'name', 'price']),
            ],
            $provider->getModels(),
        );

        return $this->ok($favorites, ['pagination' => $this->paginationMeta($provider->getPagination())]);
    }

    public function actionAdd(): array
    {
        $productId = $this->bodyInt('product_id', 1, PHP_INT_MAX);
        $added = FavoriteService::add((int) Yii::$app->user->id, $productId);

        return $this->ok(['product_id' => $productId, 'added' => $added], [], $added ? 201 : 200);
    }

    public function actionRemove(int $id): array
    {
        $removed = FavoriteService::remove((int) Yii::$app->user->id, $id);

        return $this->ok(['product_id' => $id, 'removed' => $removed]);
    }
}
