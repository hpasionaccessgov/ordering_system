<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;

class AdjustPointsForm extends Model
{
    public $userId;
    public $points;
    public $remarks = '';

    public function rules(): array
    {
        return [
            [['userId', 'points', 'remarks'], 'required'],
            [['userId', 'points'], 'integer'],
            [['userId'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['userId' => 'id']],
            [['points'], 'compare', 'compareValue' => 0, 'operator' => '!='],
            [['remarks'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'userId' => 'Customer',
            'points' => 'Points (use - to deduct)',
            'remarks' => 'Reason',
        ];
    }
}
