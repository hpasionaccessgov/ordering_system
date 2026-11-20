<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * Class ProfileForm
 * @package app\models
 *
 * @property string $fullName
 * @property string $username
 * @property string $email
 */

class ProfileForm extends Model
{
    public string $fullName = '';
    public string $username = '';
    public string $email = '';
    public function rules(): array
    {
        return [
            [['fullName', 'username', 'email'], 'trim'],
            [['email'], 'filter', 'filter' => 'mb_strtolower'],
            [['fullName', 'username', 'email'], 'required'],
            [['fullName'], 'string', 'min' => 1, 'max' => 150],
            [['username'], 'string', 'min' => 3, 'max' => 59],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['username', 'email'], 'unique',
                'targetClass' => User::class,
                'filter' => static fn(ActiveQuery $query) => $query->andWhere(['<>', 'id', Yii::$app->user->id]),
            ],
        ];
    }
}
