<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends User
{
    public $q = '';
    public $statusFilter = '';
    public $roleFilter = '';
    public ?ListingFilters $filters = null;

    public function rules(): array
    {
        return [
            [['q', 'statusFilter', 'roleFilter'], 'string'],
            [['q'], 'trim'],
            [['statusFilter'], 'in', 'range' => ['0', '10', '20'], 'strict' => true],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = User::find()->with(['createdBy', 'updatedBy']);
        $this->filters = new ListingFilters();
        $this->filters->loadFilters($params, true, true, false);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ApplicationSettings::pagination(),
            'sort' => [
                'attributes' => [
                    'id', 'username', 'full_name', 'email',
                    'created_by' => [
                        'asc' => [User::usernameSortExpression('{{%users}}', 'created_by', SORT_ASC)],
                        'desc' => [User::usernameSortExpression('{{%users}}', 'created_by', SORT_DESC)],
                    ],
                    'updated_by' => [
                        'asc' => [User::usernameSortExpression('{{%users}}', 'updated_by', SORT_ASC)],
                        'desc' => [User::usernameSortExpression('{{%users}}', 'updated_by', SORT_DESC)],
                    ],
                ],
                'defaultOrder' => ['id' => SORT_ASC],
            ],
        ]);
        $this->load([
            'q' => is_string($params['q'] ?? null) ? $params['q'] : '',
            'statusFilter' => $params['status'] ?? '',
            'roleFilter' => $params['role'] ?? '',
        ], '');
        if (!$this->validate() || !$this->filters->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }
        $this->filters->apply($query);
        $query->andFilterWhere(['status' => $this->statusFilter]);
        if ($this->roleFilter !== '') {
            $query->andWhere(['id' => (new \yii\db\Query())->select('user_id')
                ->from('{{%auth_assignment}}')->where(['item_name' => $this->roleFilter])]);
        }
        if ($this->q !== '') {
            $query->andWhere(['or',
                ['like', 'username', $this->q],
                ['like', 'full_name', $this->q],
                ['like', 'email', $this->q],
            ]);
        }

        return $dataProvider;
    }
}
