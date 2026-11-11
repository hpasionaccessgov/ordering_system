<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;

class DeactivateForm extends Model
{
    public string $currentPassword = '';

    public function __construct(private readonly User $user, $config = [])
    {
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['currentPassword'], 'required'],
            ['currentPassword', 'validateCurrentPassword'],
        ];
    }

    public function validateCurrentPassword(string $attribute, $params): void
    {
        if (!$this->user->validatePassword($this->currentPassword)) {
            $this->addError($attribute, 'The current password is incorrect.');
        }
    }
}
