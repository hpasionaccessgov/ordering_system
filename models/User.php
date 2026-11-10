<?php

declare(strict_types=1);

namespace app\models;

use Override;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\base\BaseObject;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 10;
    public const STATUS_DISABLED = 20;
    public const STATUS_PENDING = 30;

    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['username', 'email', 'password_hash', 'full_name', 'auth_key', 'status'], 'required'],
            [['username'], 'string', 'min' => 3, 'max' => 59],
            [['username'], 'unique'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['password_hash'], 'string', 'max' => 255],
            [['full_name'], 'string', 'max' => 150],
            [['auth_key'], 'string', 'max' => 32],
            [['status'], 'integer'],
            ['status', 'in', 'range' => [
                self::STATUS_INACTIVE,
                self::STATUS_ACTIVE,
                self::STATUS_DISABLED,
                self::STATUS_PENDING,
            ]],
        ];
    }

    public static function findIdentity($id): ?static
    {
        return static::findOne([
            'id' => $id,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        return null; // Not implemented
    }
    public static function findByUsername($username): ?static
    {
        return static::findOne([
            'username' => $username,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    #[Override]
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return is_string($authKey) && hash_equals($this->auth_key, $authKey);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString(32);
    }
}
