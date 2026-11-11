<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;

class ProfileForm extends Model
{
    public string $fullName = '';

    public function rules(): array
    {
        return [
            [['fullName'], 'required'],
            [['fullName'], 'trim'],
            [['fullName'], 'string', 'min' => 1, 'max' => 150],
        ];
    }
}
