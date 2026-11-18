<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Class RoleForm
 * @package app\models
 *
 * @property string $name
 * @property string $description
 */

class RoleForm extends Model
{
    public $name = '';
    public $description = '';

    public function beforeValidate(): bool
    {
        if (is_string($this->name)) {
            $this->name = trim($this->name);
        }
        if (is_string($this->description)) {
            $this->description = trim($this->description);
        }

        return parent::beforeValidate();
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
            [
                ['name'],
                'match',
                'pattern' => '/^[a-z][a-z0-9._-]{2,49}$/',
                'message' => 'Start with a lowercase letter; use 3-50 lowercase letters, numbers, dots, dashes, or underscores.',
            ],
            [['name'], 'validateUniqueName'],
            [['description'], 'string', 'max' => 255],
        ];
    }

    public function validateUniqueName(string $attribute): void
    {
        if ($this->hasErrors($attribute) || !is_string($this->$attribute)) {
            return;
        }

        $auth = Yii::$app->authManager;
        if (
            $auth->getRole($this->$attribute) !== null
            || $auth->getPermission($this->$attribute) !== null
        ) {
            $this->addError($attribute, 'This RBAC item name is already in use.');
        }
    }
}
