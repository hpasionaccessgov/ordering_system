<?php

declare(strict_types=1);

namespace app\models;

use Override;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * Class User
 * @package app\models
 *
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $full_name
 * @property string $auth_key
 * @property int $status
 * @property User|null $createdBy
 * @property User|null $updatedBy
 */

class User extends ActiveRecord implements IdentityInterface
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 10;
    public const STATUS_DISABLED = 20;
    public const SCENARIO_ADMIN_PROFILE_UPDATE = 'adminProfileUpdate';

    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
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
            ]],
        ];
    }

    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_ADMIN_PROFILE_UPDATE] = ['username', 'email', 'full_name', 'status'];

        return $scenarios;
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
            'username' => trim($username),
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public static function findByLoginIdentifier(string $identifier): ?static
    {
        $identifier = trim($identifier);

        return static::find()
            ->where([
                'or',
                ['username' => $identifier],
                ['email' => mb_strtolower($identifier)],
            ])
            ->one();
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

    public function getCreatedBy(): ActiveQuery
    {
        return $this->hasOne(self::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy(): ActiveQuery
    {
        return $this->hasOne(self::class, ['id' => 'updated_by']);
    }

    public function getFavorites(): ActiveQuery
    {
        return $this->hasMany(Favorite::class, ['user_id' => 'id']);
    }

    public function getReviews(): ActiveQuery
    {
        return $this->hasMany(ProductReview::class, ['user_id' => 'id']);
    }

    /**
     * ORDER BY expression that sorts a table by the username of its created_by/updated_by user,
     * without joining (a self-join would make unqualified columns ambiguous).
     *
     * @param string $relatedTable the source table, e.g. '{{%category}}'.
     * @param string $foreignKey the source column, e.g. 'created_by'.
     * @param int $direction SORT_ASC or SORT_DESC.
     */
    public static function usernameSortExpression(string $relatedTable, string $foreignKey, int $direction): Expression
    {
        $db = Yii::$app->db;
        $users = $db->quoteSql(self::tableName());
        $alias = $db->quoteSql('[[audit_user]]');
        $order = $direction === SORT_DESC ? 'DESC' : 'ASC';

        return new Expression(sprintf(
            '(SELECT %s FROM %s AS %s WHERE %s = %s) %s',
            $db->quoteSql('[[audit_user]].[[username]]'),
            $users,
            $alias,
            $db->quoteSql('[[audit_user]].[[id]]'),
            $db->quoteSql($relatedTable . '.[[' . $foreignKey . ']]'),
            $order,
        ));
    }
}
