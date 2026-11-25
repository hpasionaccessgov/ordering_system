<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class ActivityLog extends ActiveRecord
{
    public const VISIT = 'visit';
    public const LOGIN = 'login';
    public const LOGOUT = 'logout';
    public const REWARD_CREATED = 'reward_created';
    public const REWARD_UPDATED = 'reward_updated';
    public const REWARD_DISABLED = 'reward_disabled';
    public const POINTS_ADJUSTED = 'points_adjusted';
    public const REWARD_REDEEMED = 'reward_redeemed';

    public static function eventLabels(): array
    {
        return [
            self::VISIT => 'Page visits',
            self::LOGIN => 'Logins',
            self::LOGOUT => 'Logouts',
            self::REWARD_CREATED => 'Reward created',
            self::REWARD_UPDATED => 'Reward updated',
            self::REWARD_DISABLED => 'Reward disabled',
            self::POINTS_ADJUSTED => 'Points adjusted',
            self::REWARD_REDEEMED => 'Reward redeemed',
        ];
    }

    public static function tableName(): string
    {
        return '{{%activity_log}}';
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
