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

    public static function tableName(): string
    {
        return '{{%activity_log}}';
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
