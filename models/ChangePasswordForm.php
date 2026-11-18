<?php

declare(strict_types=1);

namespace app\models;

/**
 * Class ChangePasswordForm
 * @package app\models
 *
 * @property string $currentPassword
 * @property string $newPassword
 * @property string $confirmPassword
 */

use yii\base\Model;

class ChangePasswordForm extends Model
{
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $confirmPassword = '';

    public function __construct(
        private readonly User $user,
        $config = [],
    ) {
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['currentPassword', 'newPassword', 'confirmPassword'], 'required'],
            ['currentPassword', 'validateCurrentPassword'],
            [['newPassword'], 'string', 'min' => 12, 'max' => 72],
            [
                'confirmPassword',
                'compare',
                'compareAttribute' => 'newPassword'
            ],
        ];
    }

    public function validateCurrentPassword(string $attribute, $params): void
    {
        if (!$this->hasErrors() && !$this->user->validatePassword($this->currentPassword)) {
            $this->addError($attribute, 'The current password is incorrect.');
        }
    }
}
