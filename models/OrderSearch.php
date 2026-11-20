<?php

declare(strict_types=1);

namespace app\models;

use app\services\OrderAccessPolicy;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class OrderSearch extends Order
{
    public $q = '';
    public $statusFilter = '';
    public $filters;

    public function rules(): array
    {
        return [
            [['q', 'statusFilter'], 'string'],
            [['q'], 'trim'],
            [['statusFilter'], 'in', 'range' => Order::statusValues()],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params, int|string $userId, bool $canViewAllOrders): ActiveDataProvider
    {
        $query = OrderAccessPolicy::scopeToOwner(
            Order::find()->with(['user', 'createdBy', 'updatedBy']),
            $userId,
            $canViewAllOrders,
        );
        $this->filters = new ListingFilters();
        $this->filters->loadFilters($params, $canViewAllOrders, true, true);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ApplicationSettings::pagination(),
            'sort' => [
                'attributes' => [
                    'id', 'order_number', 'status', 'total_amount', 'created_at', 'updated_at',
                    'created_by' => [
                        'asc' => [User::usernameSortExpression('{{%orders}}', 'created_by', SORT_ASC)],
                        'desc' => [User::usernameSortExpression('{{%orders}}', 'created_by', SORT_DESC)],
                    ],
                    'updated_by' => [
                        'asc' => [User::usernameSortExpression('{{%orders}}', 'updated_by', SORT_ASC)],
                        'desc' => [User::usernameSortExpression('{{%orders}}', 'updated_by', SORT_DESC)],
                    ],
                ],
                'defaultOrder' => ['created_at' => SORT_DESC, 'id' => SORT_DESC],
            ],
        ]);
        $status = $params['status'] ?? '';
        $this->load([
            'q' => is_string($params['q'] ?? null) ? $params['q'] : '',
            'statusFilter' => is_string($status) && in_array($status, Order::statusValues(), true) ? $status : '',
        ], '');
        if (!$this->validate() || !$this->filters->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }
        $this->filters->apply($query, 'total_amount');
        if ($this->q !== '') {
            $conditions = [
                'or',
                ['like', 'status', $this->q],
                ['like', 'order_number', $this->q],
                ['user_id' => User::find()->select('id')->andWhere([
                    'or',
                    ['like', 'username', $this->q],
                    ['like', 'full_name', $this->q],
                    ['like', 'email', $this->q],
                ])],
            ];
            if (ctype_digit($this->q)) {
                $conditions[] = ['id' => $this->q];
            }
            $query->andWhere($conditions);
        }
        if ($this->statusFilter !== '') {
            $query->andWhere(['status' => $this->statusFilter]);
        }

        return $dataProvider;
    }
}
