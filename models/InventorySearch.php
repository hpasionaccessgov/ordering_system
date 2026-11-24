<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * InventorySearch represents the model behind the search form of `app\models\Inventory`.
 */
class InventorySearch extends Inventory
{
    /** @var string|null */
    public $q;
    /** @var ListingFilters */
    public $filters;

    public function rules(): array
    {
        return [
            [['id', 'status', 'current_stock', 'minimum_stock', 'created_at', 'updated_at'], 'integer'],
            [['name', 'description', 'unit'], 'safe'],
            [['q'], 'string'],
            [['q'], 'trim', 'skipOnArray' => true, 'skipOnEmpty' => true],
        ];
    }

    public function scenarios(): array
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search(array $params, ?string $formName = null): ActiveDataProvider
    {
        $query = Inventory::find();
        $this->filters = new ListingFilters();
        $this->filters->loadFilters($params, false, false, false);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ApplicationSettings::pagination(),
            'sort' => [
                'attributes' => [
                    'id',
                    'name',
                    'unit',
                    'current_stock',
                    'minimum_stock',
                    'status',
                    'created_at',
                    'updated_at',
                ],
                'defaultOrder' => ['name' => SORT_ASC, 'id' => SORT_ASC],
            ],
        ]);

        $this->load($params, $formName);
        if (isset($params['q'])) {
            $this->q = $params['q'];
        }

        if (!$this->validate() || !$this->filters->validate()) {
            $query->andWhere('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'current_stock' => $this->current_stock,
            'minimum_stock' => $this->minimum_stock,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'unit', $this->unit]);
        $this->filters->apply($query);

        if ($this->q !== null && $this->q !== '') {
            $conditions = [
                'or',
                ['like', 'name', $this->q],
                ['like', 'description', $this->q],
                ['like', 'unit', $this->q],
            ];
            if (ctype_digit($this->q)) {
                $conditions[] = ['id' => $this->q];
            }
            $query->andWhere($conditions);
        }

        return $dataProvider;
    }
}
