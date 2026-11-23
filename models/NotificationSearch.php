<?php

declare(strict_types=1);

namespace app\models;

use app\models\Order;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class NotificationSearch extends Notification
{
    public $q = '';
    public $readFilter = '';

    public function rules(): array
    {
        return [
            [['q', 'status', 'readFilter'], 'trim'],
            [['q'], 'string'],
            [['status'], 'string', 'max' => 30],
            [['readFilter'], 'in', 'range' => ['', 'unread', 'read']],
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
        ], '');

        if (!$this->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }

        if ($this->q !== '') {
            $query->andWhere([
                'or',
                ['like', 'status', $this->q],
                ['order_id' => Order::find()->select('id')->andWhere(['like', 'order_number', $this->q])],
            ]);
        }
        if ($this->status !== '') {
            $query->andWhere(['status' => $this->status]);
        }
        if ($this->readFilter === 'unread') {
            $query->andWhere(['read_at' => null]);
        } elseif ($this->readFilter === 'read') {
            $query->andWhere(['not', ['read_at' => null]]);
        }

        return $dataProvider;
    }
}
