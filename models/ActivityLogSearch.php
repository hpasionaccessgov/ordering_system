<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ActivityLogSearch extends ActivityLog
{
    public $username = '';
    public $filters;

    public function rules(): array
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['username', 'event', 'route'], 'string'],
            [['username', 'route'], 'trim', 'skipOnArray' => true],
            [['event'], 'in', 'range' => [self::VISIT, self::LOGIN, self::LOGOUT]],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = ActivityLog::find()->with('user');
        $this->filters = new ListingFilters();
        $this->filters->loadFilters(
            array_intersect_key($params, array_flip(['created_from', 'created_to'])),
            false,
            true,
            false,
        );
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ApplicationSettings::pagination(),
            'sort' => [
                'attributes' => [
                    'id', 'user_id', 'event', 'route', 'created_at',
                    'username' => [
                        'asc' => [User::usernameSortExpression('{{%activity_log}}', 'user_id', SORT_ASC)],
                        'desc' => [User::usernameSortExpression('{{%activity_log}}', 'user_id', SORT_DESC)],
                    ],
                ],
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);
        $this->load($params);
        if (array_key_exists('event', $params)) {
            $this->event = $params['event'];
        }
        if (!$this->validate() || !$this->filters->validate()) {
            $query->andWhere('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id, 'user_id' => $this->user_id, 'event' => $this->event])
            ->andFilterWhere(['like', 'route', $this->route]);
        if ($this->username !== '') {
            $query->andWhere(['user_id' => User::find()->select('id')->where(['like', 'username', $this->username])]);
        }
        $this->filters->apply($query);

        return $dataProvider;
    }
}
