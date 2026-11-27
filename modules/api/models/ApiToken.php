<?php

declare(strict_types=1);

namespace app\modules\api\models;

use app\models\User;
use yii\db\ActiveRecord;

/**
 * @property int $user_id
 * @property string $token_hash
 * @property int $expires_at
 * @property int|null $revoked_at
 * @property int|null $last_used_at
 * @property int $created_at
 * @property int $updated_at
 */
final class ApiToken extends ActiveRecord
{
    public const TTL_SECONDS = 86400;

    public static function tableName(): string
    {
        return '{{%api_tokens}}';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'token_hash', 'expires_at', 'created_at', 'updated_at'], 'required'],
            [['user_id', 'expires_at', 'revoked_at', 'last_used_at', 'created_at', 'updated_at'], 'integer'],
            [['token_hash'], 'string', 'max' => 64],
            [['token_hash'], 'unique'],
        ];
    }

    /**
     * @return array{token: string, expires_at: int}
     */
    public static function issue(int $userId): array
    {
        $token = bin2hex(random_bytes(32));
        $now = time();
        $record = new self([
            'user_id' => $userId,
            'token_hash' => self::hash($token),
            'expires_at' => $now + self::TTL_SECONDS,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        if (!$record->save()) {
            throw new \RuntimeException('Could not issue an API token.');
        }

        return ['token' => $token, 'expires_at' => $record->expires_at];
    }

    public static function findActive(string $token): ?self
    {
        if ($token === '') {
            return null;
        }
        $record = self::findOne(['token_hash' => self::hash($token)]);
        if (
            $record === null
            || $record->revoked_at !== null
            || (int) $record->expires_at <= time()
        ) {
            return null;
        }

        return $record;
    }

    public static function findActiveUser(string $token): ?User
    {
        $record = self::findActive($token);
        if ($record === null) {
            return null;
        }
        $record->updateAttributes(['last_used_at' => time()]);

        return User::findIdentity($record->user_id);
    }

    public static function revoke(string $token): void
    {
        $record = self::findOne(['token_hash' => self::hash($token)]);
        if ($record !== null && $record->revoked_at === null) {
            $record->updateAttributes(['revoked_at' => time()]);
        }
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
