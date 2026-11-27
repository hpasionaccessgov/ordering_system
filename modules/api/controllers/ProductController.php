<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use app\models\ApplicationSettings;
use app\models\Product;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

final class ProductController extends ApiController
{
    public bool $authRequired = false;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['index', 'view'], 'allow' => true, 'roles' => ['?', '@']],
            ],
        ];

        return $behaviors;
    }

    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
            'view' => ['GET'],
        ];
    }

    public function actionIndex(): array
    {
        $provider = new ActiveDataProvider([
            'query' => Product::find()
                ->where(['status' => Product::STATUS_ACTIVE])
                ->orderBy(['name' => SORT_ASC]),
            'pagination' => ApplicationSettings::pagination(),
        ]);

        $products = array_map(
            fn(Product $product): array => $this->serializeProduct($product),
            $provider->getModels(),
        );

        return $this->ok($products, ['pagination' => $this->paginationMeta($provider->getPagination())]);
    }

    public function actionView(int $id): array
    {
        $product = Product::findOne(['id' => $id, 'status' => Product::STATUS_ACTIVE]);
        if ($product === null) {
            $this->fail(404, 'not_found', 'Product not found.');
        }

        return $this->ok($this->serializeProduct($product));
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeProduct(Product $product): array
    {
        $data = $product->toArray([
            'id',
            'name',
            'description',
            'price',
            'category_id',
            'is_featured',
            'is_special',
            'status',
        ]);
        $data['image_url'] = $product->getImageUrl();

        return $data;
    }
}
