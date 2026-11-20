<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends User
{
    public $q = '';

    public function rules(): array
    {
        return [[['q'], 'string'], [['q'], 'trim']];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = User::find()->with(['createdBy', 'updatedBy']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 8, 'pageSizeLimit' => [8, 8]],
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
        $this->load(['q' => is_string($params['q'] ?? null) ? $params['q'] : ''], '');
        if ($this->validate() && $this->q !== '') {
            $query->andWhere(['or',
                ['like', 'username', $this->q],
                ['like', 'full_name', $this->q],
                ['like', 'email', $this->q],
            ]);
        }

        return $dataProvider;
    }
}
