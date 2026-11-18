<?php

declare(strict_types=1);

namespace app\models;

use Yii;

/**
 * Class AdminUserCreateForm
 * @package app\models
 *
 * @property string $roleName
 */

class AdminUserCreateForm extends RegisterForm
{
    public $roleName = 'customer';

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['roleName'], 'required'],
            [['roleName'], 'validateRoleName'],
        ]);
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'roleName' => 'Role',
        ]);
    }

    public function validateRoleName(string $attribute): void
    {
        if (
            $this->hasErrors($attribute)
            || !is_string($this->$attribute)
            || Yii::$app->authManager->getRole($this->$attribute) === null
        ) {
            $this->addError($attribute, 'Select an existing role.');
        }
    }
}
