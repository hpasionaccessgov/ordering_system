<?php

declare(strict_types=1);

namespace app\models;

use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;

class ListingFilters extends Model
{
    public $created_by_name = '';
    public $updated_by_name = '';
    public $created_from = '';
    public $created_to = '';
    public $updated_from = '';
    public $updated_to = '';
    public $min_amount = '';
    public $max_amount = '';

    public function rules(): array
    {
        return [
            [['created_by_name', 'updated_by_name'], 'string', 'max' => 59],
            [['created_by_name', 'updated_by_name'], 'trim', 'skipOnArray' => true],
            [['created_from', 'created_to', 'updated_from', 'updated_to'], 'date', 'format' => 'php:Y-m-d'],
            [['min_amount', 'max_amount'], 'number', 'min' => 0],
            [['max_amount'], 'compare', 'compareAttribute' => 'min_amount', 'operator' => '>=', 'type' => 'number', 'skipOnEmpty' => true, 'when' => fn(self $model): bool => !$model->hasErrors('min_amount') && $model->min_amount !== '' && $model->min_amount !== null],
            [['created_to'], 'compare', 'compareAttribute' => 'created_from', 'operator' => '>=', 'skipOnEmpty' => true, 'when' => fn(self $model): bool => !$model->hasErrors('created_from') && $model->created_from !== '' && $model->created_from !== null],
            [['updated_to'], 'compare', 'compareAttribute' => 'updated_from', 'operator' => '>=', 'skipOnEmpty' => true, 'when' => fn(self $model): bool => !$model->hasErrors('updated_from') && $model->updated_from !== '' && $model->updated_from !== null],
        ];
    }

    public function loadFilters(array $params, bool $audit, bool $dates, bool $amount): void
    {
        $allowed = [];
        if ($audit) {
            $allowed = array_merge($allowed, ['created_by_name', 'updated_by_name']);
        }
        if ($dates) {
            $allowed = array_merge($allowed, ['created_from', 'created_to', 'updated_from', 'updated_to']);
        }
        if ($amount) {
            $allowed = array_merge($allowed, ['min_amount', 'max_amount']);
        }
        $this->load(array_intersect_key($params, array_flip($allowed)), '');
        foreach ($allowed as $attribute) {
            if ($this->$attribute === null) {
                $this->$attribute = '';
            }
        }
    }

    public function apply(ActiveQuery $query, ?string $amountColumn = null): void
    {
        foreach (['created_by' => $this->created_by_name, 'updated_by' => $this->updated_by_name] as $column => $name) {
            if ($name !== '') {
                $query->andWhere([$column => User::find()->select('id')->where(['like', 'username', $name])]);
            }
        }
        $zone = new DateTimeZone(Yii::$app->formatter->timeZone ?: Yii::$app->timeZone);
        foreach (['created' => 'created_at', 'updated' => 'updated_at'] as $prefix => $column) {
            $from = $this->{$prefix . '_from'};
            $to = $this->{$prefix . '_to'};
            if ($from !== '') {
                $query->andWhere(['>=', $column, (new DateTimeImmutable($from, $zone))->getTimestamp()]);
            }
            if ($to !== '') {
                $query->andWhere(['<', $column, (new DateTimeImmutable($to, $zone))->modify('+1 day')->getTimestamp()]);
            }
        }
        if ($amountColumn !== null) {
            $query->andFilterWhere(['>=', $amountColumn, $this->min_amount])
                ->andFilterWhere(['<=', $amountColumn, $this->max_amount]);
        }
    }
}
