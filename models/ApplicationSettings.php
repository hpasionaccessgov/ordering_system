<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int|string $card_page_size
 * @property int|string $table_page_size
 * @property int|string $idle_minutes
 * @property int|string $login_max_attempts
 * @property int|string $login_reset_minutes
 * @property int|string $login_lock_minutes
 */
class ApplicationSettings extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%application_settings}}';
    }

    public static function current(): self
    {
        return self::findOne(1) ?? throw new \RuntimeException('Application settings are missing. Run the database migrations.');
    }

    public function rules(): array
    {
        return [
            [['card_page_size', 'table_page_size', 'idle_minutes', 'login_max_attempts', 'login_reset_minutes', 'login_lock_minutes'], 'required'],
            [['card_page_size', 'table_page_size'], 'integer', 'min' => 1, 'max' => 100],
            [['idle_minutes', 'login_reset_minutes', 'login_lock_minutes'], 'integer', 'min' => 1, 'max' => 1440],
            [['login_max_attempts'], 'integer', 'min' => 1, 'max' => 100],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'card_page_size' => 'Cards per page',
            'table_page_size' => 'Table and list items per page',
            'idle_minutes' => 'Inactivity timeout (minutes)',
            'login_max_attempts' => 'Maximum failed login attempts',
            'login_reset_minutes' => 'Failed-attempt reset window (minutes)',
            'login_lock_minutes' => 'Login lockout duration (minutes)',
        ];
    }

    public static function pagination(bool $cards = false): array
    {
        $settings = self::current();
        $size = (int) ($cards ? $settings->card_page_size : $settings->table_page_size);
        return ['pageSize' => $size, 'pageSizeLimit' => [$size, $size]];
    }
}
