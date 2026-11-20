<?php

declare(strict_types=1);

namespace app\models;

use app\services\ProductCatalogPolicy;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ProductSearch extends Product
{
    public $q = '';
    public $categoryId = '';
    public $catalogStatus = 'all';
    public $minPrice;
    public $maxPrice;

    public function rules(): array
    {
        return [
            [['q', 'categoryId', 'catalogStatus'], 'string'],
            [['q'], 'trim'],
            [['minPrice', 'maxPrice'], 'safe'],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params, bool $canViewProducts, bool $canManageProducts): ActiveDataProvider
    {
        return $this->buildSearch($params, false, $canViewProducts, $canManageProducts);
    }

    public function searchMenu(array $params): ActiveDataProvider
    {
        return $this->buildSearch($params, true, true, false);
    }

    private function buildSearch(array $params, bool $publicMenu, bool $canViewProducts, bool $canManageProducts): ActiveDataProvider
    {
        $categoryId = $params['category_id'] ?? '';
        $requestedStatus = $params['status'] ?? 'all';
        $this->load([
            'q' => is_string($params['q'] ?? null) ? $params['q'] : '',
            'categoryId' => is_scalar($categoryId) ? trim((string) $categoryId) : '',
            'catalogStatus' => ProductCatalogPolicy::normalizeStatus(
                $canManageProducts,
                is_string($requestedStatus) ? $requestedStatus : 'all',
            ),
            'minPrice' => $params['min_price'] ?? null,
            'maxPrice' => $params['max_price'] ?? null,
        ], '');

        $query = ProductCatalogPolicy::applyStatusFilter(
            Product::find()->with($publicMenu ? ['categoryRecord'] : ['categoryRecord', 'createdBy', 'updatedBy']),
            $this->catalogStatus,
        );
        if (!$canViewProducts) {
            $query->andWhere(['id' => null]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $publicMenu ? 8 : 6,
                'pageSizeLimit' => $publicMenu ? [8, 8] : [6, 6],
            ],
            'sort' => [
                'attributes' => ['name', 'price', 'id'],
                'defaultOrder' => ['name' => SORT_ASC, 'id' => SORT_ASC],
            ],
        ]);
        if (!$this->validate()) {
            return $dataProvider;
        }
        if ($this->q !== '') {
            $conditions = ['or', ['like', 'name', $this->q], ['like', 'description', $this->q]];
            if (!$publicMenu) {
                $conditions[] = ['category_id' => Category::find()->select('id')->andWhere(['like', 'name', $this->q])];
            }
            $query->andWhere($conditions);
        }
        if ($this->categoryId !== '') {
            $query->andWhere(ctype_digit($this->categoryId)
                ? ['category_id' => (int) $this->categoryId]
                : ['id' => null]);
        }
        if (!$publicMenu && is_numeric($this->minPrice)) {
            $query->andWhere(['>=', 'price', (float) $this->minPrice]);
        }
        if (!$publicMenu && is_numeric($this->maxPrice)) {
            $query->andWhere(['<=', 'price', (float) $this->maxPrice]);
        }

        return $dataProvider;
    }
}
