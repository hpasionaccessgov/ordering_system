<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Category;
use app\models\OrderItem;
use app\models\Product;
use app\services\ProductCatalogPolicy;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['product.index'],
                    ],
                    [
                        'actions' => ['view'],
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
                        'actions' => ['restore'],
                        'allow' => true,
                        'roles' => ['product.update'],
                    ],
                    [
                        'actions' => ['archive'],
                        'allow' => true,
                        'roles' => ['product.archive'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['product.delete'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'archive' => ['post'],
                    'restore' => ['post'],
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $this->layout = 'admin';
        $canViewProducts = Yii::$app->user->can('product.view');
        $canManageProducts = $this->canManageArchivedProducts();
        $requestedStatus = $this->request->get('status', 'all');
        $status = ProductCatalogPolicy::normalizeStatus(
            $canManageProducts,
            is_string($requestedStatus) ? $requestedStatus : 'all',
        );

        $query = ProductCatalogPolicy::applyStatusFilter(
            Product::find()->with('categoryRecord'),
            $status,
        );
        if (!$canViewProducts) {
            $query->andWhere(['id' => null]);
        }

        $requestedSearch = $this->request->get('q', '');
        $search = is_string($requestedSearch) ? trim($requestedSearch) : '';
        $requestedCategoryId = $this->request->get('category_id', '');
        $categoryId = is_scalar($requestedCategoryId)
            ? trim((string) $requestedCategoryId)
            : '';
        $minPrice = $this->request->get('min_price');
        $maxPrice = $this->request->get('max_price');

        if ($search !== '') {
            $query->andWhere(['or',
                ['like', 'name', $search],
                ['like', 'description', $search],
                [
                    'category_id' => Category::find()
                        ->select('id')
                        ->andWhere(['like', 'name', $search]),
                ],
            ]);
        }
        if ($categoryId !== '') {
            if (ctype_digit($categoryId)) {
                $query->andWhere(['category_id' => (int) $categoryId]);
            } else {
                $query->andWhere(['id' => null]);
            }
        }
        if (is_numeric($minPrice)) {
            $query->andWhere(['>=', 'price', (float) $minPrice]);
        }
        if (is_numeric($maxPrice)) {
            $query->andWhere(['<=', 'price', (float) $maxPrice]);
        }

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => ['pageSize' => 10, 'pageSizeLimit' => [10, 10]],
                'sort' => [
                    'attributes' => ['name', 'price', 'id'],
                    'defaultOrder' => ['name' => SORT_ASC, 'id' => SORT_ASC],
                ],
            ]),
            'categories' => ArrayHelper::map(
                Category::find()->orderBy('name')->all(),
                'id',
                'name',
            ),
            'canManageProducts' => $canManageProducts,
            'canViewProducts' => $canViewProducts,
            'status' => $status,
            'search' => $search,
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
            ->select(['image_data', 'image_mime', 'image_name', 'status'])
            ->where(['id' => $id])
            ->one();

        if (
            $model === null
            || ($model->status !== Product::STATUS_ACTIVE && !$this->canManageArchivedProducts())
            || $model->image_data === null
        ) {
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

                return $this->redirect($this->getProductReturnRoute((int) $model->id));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => $this->getCategoryOptions($model),
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        if (!Yii::$app->user->can('product.view')) {
            throw new ForbiddenHttpException('You are not allowed to view this product.');
        }

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
            'categories' => $this->getCategoryOptions($model),
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

        return $this->redirect($this->getProductListReturnRoute());
    }

    public function actionRestore(int $id): Response
    {
        $model = $this->findProduct($id);
        if ($model->status !== Product::STATUS_ARCHIVED) {
            throw new BadRequestHttpException('Only archived products can be restored.');
        }

        $model->status = Product::STATUS_ACTIVE;
        if (!$model->save(false, ['status', 'updated_at'])) {
            throw new BadRequestHttpException('Product could not be restored.');
        }

        Yii::$app->session->setFlash('success', 'Product restored.');

        return $this->redirect($this->getProductListReturnRoute());
    }

    public function actionDelete(int $id): Response
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $lockedId = Yii::$app->db->createCommand(
                'SELECT id FROM {{%products}} WHERE id = :id FOR UPDATE',
                [':id' => $id],
            )->queryScalar();

            if ($lockedId === false) {
                throw new NotFoundHttpException('Product not found.');
            }

            $model = Product::findOne($lockedId);
            if ($model === null) {
                throw new NotFoundHttpException('Product not found.');
            }

            if (OrderItem::find()->where(['product_id' => $model->id])->exists()) {
                $transaction->rollBack();
                Yii::$app->session->setFlash(
                    'error',
                    'This product is part of order history and cannot be permanently deleted. Archive it instead.',
                );

                return $this->redirect($this->getProductListReturnRoute());
            }

            if ($model->delete() === false) {
                throw new BadRequestHttpException('Product could not be permanently deleted.');
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }

            throw $exception;
        }

        Yii::$app->session->setFlash('success', 'Product permanently deleted.');

        return $this->redirect($this->getProductListReturnRoute());
    }

    private function findProduct(int $id): Product
    {
        $query = Product::find()->where(['id' => $id]);
        if (!$this->canManageArchivedProducts()) {
            $query->andWhere(['status' => Product::STATUS_ACTIVE]);
        }

        $model = $query->one();

        if ($model === null) {
            throw new NotFoundHttpException('Product not found.');
        }

        return $model;
    }

    private function canManageArchivedProducts(): bool
    {
        return Yii::$app->user->can('product.update')
            || Yii::$app->user->can('product.archive')
            || Yii::$app->user->can('product.delete');
    }

    /**
     * @return array<int, string>
     */
    private function getCategoryOptions(Product $product): array
    {
        $query = Category::find()->where(['status' => Category::STATUS_ACTIVE]);
        if ($product->category_id !== null) {
            $query->orWhere(['id' => $product->category_id]);
        }

        return ArrayHelper::map($query->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * @return array|string
     */
    private function getProductReturnRoute(int $id): array|string
    {
        if (Yii::$app->user->can('product.view')) {
            return ['view', 'id' => $id];
        }
        if (Yii::$app->user->can('product.index')) {
            return ['index'];
        }

        return ['/site/dashboard'];
    }

    /**
     * @return array|string
     */
    private function getProductListReturnRoute(): array|string
    {
        return Yii::$app->user->can('product.index')
            ? ['index']
            : ['/site/dashboard'];
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
