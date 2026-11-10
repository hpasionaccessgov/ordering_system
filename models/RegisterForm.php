<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;

class RegisterForm extends Model
{
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirm = '';
    public string $fullName = '';

    public function rules(): array
{
    return [
        [['username', 'email', 'fullName', 'password', 'passwordConfirm'], 'required'],
        [['username', 'email', 'fullName'], 'trim'],
        [['username'], 'string', 'min' => 3, 'max' => 59],
        [['username'], 'unique', 'targetClass' => User::class],

        [['email'], 'string', 'max' => 254],
        [['email'], 'email'],
        [['email'], 'unique', 'targetClass' => User::class],

        [['fullName'], 'string', 'max' => 150],
        
        [['password'], 'string', 'min' => 12, 'max' => 72],
        [['passwordConfirm'], 'compare', 'compareAttribute' => 'password'],
    ];
}
}
