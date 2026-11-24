<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\ApplicationSettings;
use app\models\Favorite;
use app\services\FavoriteService;
use app\services\ReviewService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

final class FavoriteController extends Controller
{
    public $layout = 'admin';

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['index'], 'allow' => true, 'roles' => ['favorite.index']],
                    ['actions' => ['add'], 'allow' => true, 'roles' => ['favorite.add']],
                    ['actions' => ['remove'], 'allow' => true, 'roles' => ['favorite.remove']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST'],
                    'remove' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Favorite::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->with('product')
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ApplicationSettings::pagination(true),
            'sort' => false,
        ]);
        $products = [];
        foreach ($dataProvider->getModels() as $favorite) {
            if ($favorite->product !== null) {
                $products[] = $favorite->product;
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'favoriteIds' => FavoriteService::ids((int) Yii::$app->user->id),
            'ratingSummaries' => ReviewService::summaries(
                array_map(static fn($product): int => (int) $product->id, $products),
            ),
        ]);
    }

    public function actionAdd(int $id): Response
    {
        if (FavoriteService::add((int) Yii::$app->user->id, $id)) {
            Yii::$app->session->setFlash('success', 'Added to your favorites.');
        } else {
            Yii::$app->session->setFlash('info', 'This product is already in your favorites.');
        }

        return $this->redirect($this->returnUrl());
    }

    public function actionRemove(int $id): Response
    {
        if (FavoriteService::remove((int) Yii::$app->user->id, $id)) {
            Yii::$app->session->setFlash('success', 'Removed from your favorites.');
        } else {
            Yii::$app->session->setFlash('info', 'This product was not in your favorites.');
        }

        return $this->redirect($this->returnUrl());
    }

    private function returnUrl(): array|string
    {
        $referrer = $this->request->referrer;

        return is_string($referrer)
            && str_starts_with($referrer, '/')
            && !str_starts_with($referrer, '//')
            ? $referrer
            : ['/favorite/index'];
    }
}
