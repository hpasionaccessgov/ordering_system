<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Category;

/**
 * CategorySearch represents the model behind the search form of `app\models\Category`.
 */
class CategorySearch extends Category
{
    /** @var string|null */
    public $q;
    /** @var \app\models\ListingFilters $filters */
    public $filters;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name', 'description'], 'safe'],
            [['q'], 'string'],
            [['q'], 'trim', 'skipOnArray' => true, 'skipOnEmpty' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Category::find()->with(['createdBy', 'updatedBy']);
        $this->filters = new ListingFilters();
        $this->filters->loadFilters($params, true, true, false);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 8, 'pageSizeLimit' => [8, 8]],
            'sort' => [
                'attributes' => [
                    'id',
                    'name',
                    'description',
                    'status',
                    'created_at',
                    'updated_at',
                    'created_by' => [
                        'asc' => [User::usernameSortExpression('{{%category}}', 'created_by', SORT_ASC)],
                        'desc' => [User::usernameSortExpression('{{%category}}', 'created_by', SORT_DESC)],
                    ],
                    'updated_by' => [
                        'asc' => [User::usernameSortExpression('{{%category}}', 'updated_by', SORT_ASC)],
                        'desc' => [User::usernameSortExpression('{{%category}}', 'updated_by', SORT_DESC)],
                    ],
                ],
                'defaultOrder' => ['name' => SORT_ASC, 'id' => SORT_ASC],
            ],
        ]);

        $this->load($params, $formName);
        if (isset($params['q'])) {
            $this->q = $params['q'];
        }

        if (!$this->validate() || !$this->filters->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            $query->andWhere('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description]);
        $this->filters->apply($query);

        if ($this->q !== null && $this->q !== '') {
            $conditions = [
                'or',
                ['like', 'name', $this->q],
                ['like', 'description', $this->q],
            ];
            if (ctype_digit($this->q)) {
                $conditions[] = ['id' => $this->q];
            }
            $query->andWhere($conditions);
        }

        return $dataProvider;
    }
}
