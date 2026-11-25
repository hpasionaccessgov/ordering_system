<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class NotificationSearch extends Notification
{
    public $q = '';
    public $readFilter = '';
    public $categoryFilter = '';
    public $sortFilter = 'latest';

    public function rules(): array
    {
        return [
            [['q', 'status', 'readFilter', 'categoryFilter', 'sortFilter'], 'trim'],
            [['q', 'status', 'categoryFilter', 'sortFilter'], 'string'],
            [['status', 'categoryFilter'], 'string', 'max' => 30],
            [['readFilter'], 'in', 'range' => ['', 'unread', 'read']],
            [['categoryFilter'], 'in', 'range' => array_merge([''], Notification::categoryValues())],
            [['sortFilter'], 'in', 'range' => ['latest', 'oldest', 'unread']],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params, int|string $userId, bool $canViewAll): ActiveDataProvider
    {
        $query = Notification::visibleTo($userId, $canViewAll)->with('order');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ApplicationSettings::pagination(),
            'sort' => false,
        ]);

        $this->load([
            'q' => is_string($params['q'] ?? null) ? $params['q'] : '',
            'status' => is_string($params['status'] ?? null) ? $params['status'] : '',
            'readFilter' => is_string($params['read'] ?? null) ? $params['read'] : '',
            'categoryFilter' => is_string($params['category'] ?? null) ? $params['category'] : '',
            'sortFilter' => is_string($params['sort'] ?? null) ? $params['sort'] : 'latest',
        ], '');

        if (!$this->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }

        if ($this->q !== '') {
            $query->andWhere([
                'or',
                ['like', 'status', $this->q],
                ['like', 'type', $this->q],
                ['like', 'message', $this->q],
                ['order_id' => Order::find()->select('id')->andWhere(['like', 'order_number', $this->q])],
            ]);
        }
        if ($this->status !== '') {
            $query->andWhere(['status' => $this->status]);
        }
        if ($this->categoryFilter !== '') {
            $query->andWhere(['category' => $this->categoryFilter]);
        }
        if ($this->readFilter === 'unread') {
            $query->andWhere(['read_at' => null]);
        } elseif ($this->readFilter === 'read') {
            $query->andWhere(['not', ['read_at' => null]]);
        }

        if ($this->sortFilter === 'oldest') {
            $query->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC]);
        } elseif ($this->sortFilter === 'unread') {
            $query->orderBy(['read_at' => SORT_ASC, 'created_at' => SORT_DESC, 'id' => SORT_DESC]);
        } else {
            $query->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
        }

        return $dataProvider;
    }
}
