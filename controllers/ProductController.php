<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Product;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['product.view'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['product.create'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['product.update'],
                    ],
                    [
                        'actions' => ['archive'],
                        'allow' => true,
                        'roles' => ['product.archive'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'archive' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => Product::find()
                    ->where(['status' => Product::STATUS_ACTIVE])
                    ->orderBy(['name' => SORT_ASC]),
            ]),
        ]);
    }

    public function actionView(int $id): string
    {
        $this->layout = 'admin';

        return $this->render('view', [
            'model' => $this->findProduct($id),
        ]);
    }

    public function actionCreate(): Response|string
    {
        $this->layout = 'admin';
        $model = new Product();
        $model->status = Product::STATUS_ACTIVE;

        if ($model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Product created.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $this->layout = 'admin';
        $model = $this->findProduct($id);

        if ($model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Product updated.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionArchive(int $id): Response
    {
        $model = $this->findProduct($id);
        $model->status = Product::STATUS_ARCHIVED;

        if (!$model->save(false, ['status', 'updated_at'])) {
            throw new BadRequestHttpException('Product could not be archived.');
        }

        Yii::$app->session->setFlash('success', 'Product archived.');

        return $this->redirect(['index']);
    }

    private function findProduct(int $id): Product
    {
        $model = Product::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Product not found.');
        }

        return $model;
    }
}