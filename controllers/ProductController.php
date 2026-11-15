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
use yii\web\UploadedFile;

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
                        'actions' => ['image'],
                        'allow' => true,
                        'roles' => ['?', 'product.view'],
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
        $query = Product::find()->where(['status' => Product::STATUS_ACTIVE]);
        $search = trim((string) $this->request->get('q', ''));
        $category = trim((string) $this->request->get('category', ''));
        $minPrice = $this->request->get('min_price');
        $maxPrice = $this->request->get('max_price');

        if ($search !== '') {
            $query->andWhere(['or',
                ['like', 'name', $search],
                ['like', 'description', $search],
                ['like', 'category', $search],
            ]);
        }
        if ($category !== '') {
            $query->andWhere(['category' => $category]);
        }
        if (is_numeric($minPrice)) {
            $query->andWhere(['>=', 'price', (float) $minPrice]);
        }
        if (is_numeric($maxPrice)) {
            $query->andWhere(['<=', 'price', (float) $maxPrice]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query->orderBy(['name' => SORT_ASC]),
            ]),
            'categories' => Product::find()->select('category')->distinct()->orderBy('category')->column(),
        ]);
    }

    public function actionView(int $id): string
    {
        $this->layout = 'admin';

        return $this->render('view', [
            'model' => $this->findProduct($id),
        ]);
    }

    public function actionImage(int $id): Response
    {
        $model = Product::find()
            ->select(['image_data', 'image_mime', 'image_name'])
            ->where(['id' => $id, 'status' => Product::STATUS_ACTIVE])
            ->one();

        if ($model === null || $model->image_data === null) {
            throw new NotFoundHttpException('Product image not found.');
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set(
            'Content-Type',
            $model->image_mime ?: 'application/octet-stream',
        );
        Yii::$app->response->headers->set('Content-Disposition', 'inline');
        Yii::$app->response->headers->set('Cache-Control', 'public, max-age=86400');
        Yii::$app->response->data = $model->image_data;

        return Yii::$app->response;
    }

    public function actionCreate(): Response|string
    {
        $this->layout = 'admin';
        $model = new Product();
        $model->status = Product::STATUS_ACTIVE;

        if ($this->request->isPost && $model->load($this->postAttributes())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($this->saveProduct($model)) {
                Yii::$app->session->setFlash('success', 'Product created.');

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $this->layout = 'admin';
        $model = $this->findProduct($id);

        if ($this->request->isPost && $model->load($this->postAttributes())) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if ($this->saveProduct($model)) {
                Yii::$app->session->setFlash('success', 'Product updated.');

                return $this->redirect(['view', 'id' => $model->id]);
            }
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

    private function saveProduct(Product $model): bool
    {
        if (!$model->validate()) {
            return false;
        }

        if ($model->imageFile !== null) {
            $imageData = file_get_contents($model->imageFile->tempName);
            if ($imageData === false) {
                throw new BadRequestHttpException('Product image could not be read.');
            }

            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($model->imageFile->tempName);
            if (!is_string($mime)) {
                throw new BadRequestHttpException('Product image type could not be detected.');
            }

            $model->image_data = $imageData;
            $model->image_mime = $mime;
            $model->image_name = $model->imageFile->name;
            $model->image_path = null;
        }

        if (!$model->save(false)) {
            return false;
        }

        return true;
    }

    /**
     * Exclude file input from mass assignment; UploadedFile handles it separately.
     *
     * @return array<string, mixed>
     */
    private function postAttributes(): array
    {
        $post = $this->request->post();
        if (isset($post['Product']) && is_array($post['Product'])) {
            unset($post['Product']['imageFile']);
        }

        return $post;
    }
}
