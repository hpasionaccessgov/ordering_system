<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class LoyaltyTransactionSearch extends Model
{
    public $q = '';
    public $user_id;
    public $transaction_type = '';
    public $created_from = '';
    public $created_to = '';

    public function rules(): array
    {
        return [
            [['user_id'], 'integer', 'min' => 1],
            [['transaction_type'], 'in', 'range' => array_keys(LoyaltyPointTransaction::typeLabels()), 'skipOnEmpty' => true],
            [['q', 'created_from', 'created_to'], 'trim'],
            [['q'], 'string', 'max' => 100],
            [['created_from', 'created_to'], 'date', 'format' => 'php:Y-m-d', 'skipOnEmpty' => true],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params, bool $redemptionsOnly = false): ActiveDataProvider
    {
        $query = LoyaltyPointTransaction::find()->with(['user', 'order']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ApplicationSettings::pagination(),
            'sort' => [
                'attributes' => ['id', 'user_id', 'points', 'transaction_type', 'created_at'],
                'defaultOrder' => ['created_at' => SORT_DESC, 'id' => SORT_DESC],
            ],
        ]);

        $this->load([
            'q' => is_string($params['q'] ?? null) ? $params['q'] : '',
            'user_id' => $params['user_id'] ?? null,
            'transaction_type' => is_string($params['transaction_type'] ?? null) ? $params['transaction_type'] : '',
            'created_from' => is_string($params['created_from'] ?? null) ? $params['created_from'] : '',
            'created_to' => is_string($params['created_to'] ?? null) ? $params['created_to'] : '',
        ], '');

        if (!$this->validate()) {
            $query->andWhere('0=1');

            return $dataProvider;
        }

        if ($redemptionsOnly) {
            $query->andWhere(['transaction_type' => LoyaltyPointTransaction::TYPE_REDEEM]);
        } elseif ($this->transaction_type !== '') {
            $query->andWhere(['transaction_type' => $this->transaction_type]);
        }
        if ($this->user_id !== null && $this->user_id !== '') {
            $query->andWhere(['user_id' => $this->user_id]);
        }
        if ($this->q !== '') {
            $query->andWhere([
                'or',
                ['like', 'remarks', $this->q],
                ['user_id' => User::find()->select('id')->where(['like', 'username', $this->q])],
            ]);
        }
        if ($this->created_from !== '') {
            $query->andWhere(['>=', 'created_at', strtotime($this->created_from . ' 00:00:00')]);
        }
        if ($this->created_to !== '') {
            $query->andWhere(['<=', 'created_at', strtotime($this->created_to . ' 23:59:59')]);
        }

        return $dataProvider;
    }
}
