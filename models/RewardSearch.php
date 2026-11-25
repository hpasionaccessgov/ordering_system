<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class RewardSearch extends Reward
{
    public $q = '';

    public function rules(): array
    {
        return [
            [['id', 'point_cost', 'status'], 'integer'],
            [['name', 'description'], 'safe'],
            [['q'], 'string'],
            [['q'], 'trim'],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Reward::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ApplicationSettings::pagination(),
            'sort' => [
                'attributes' => ['id', 'name', 'point_cost', 'status', 'created_at', 'updated_at'],
                'defaultOrder' => ['point_cost' => SORT_ASC, 'name' => SORT_ASC],
            ],
        ]);

        $this->load($params);
        if (array_key_exists('q', $params)) {
            $this->q = $params['q'];
        }
        if (!$this->validate()) {
            $query->andWhere('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'point_cost' => $this->point_cost,
            'status' => $this->status,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);
        if ($this->q !== '') {
            $query->andWhere(['or', ['like', 'name', $this->q], ['like', 'description', $this->q]]);
        }

        return $dataProvider;
    }
}
