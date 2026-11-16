<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\db\ActiveQuery;

class Order extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    private const NEXT_STATUSES = [
        self::STATUS_PENDING => self::STATUS_CONFIRMED,
        self::STATUS_CONFIRMED => self::STATUS_PROCESSING,
        self::STATUS_PROCESSING => self::STATUS_COMPLETED,
    ];

    public static function tableName(): string
    {
        return '{{%orders}}';
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
            [['user_id', 'status', 'total_amount'], 'required'],
            [['user_id'], 'integer'],
            [['total_amount'], 'number', 'min' => 0],
            [
                ['status'],
                'in',
                'range' => [
                    self::STATUS_PENDING,
                    self::STATUS_CONFIRMED,
                    self::STATUS_PROCESSING,
                    self::STATUS_COMPLETED,
                    self::STATUS_CANCELLED,
                ],
            ],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getItems(): ActiveQuery
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    public function isEditableByCustomer(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function nextStatus(): ?string
    {
        return self::nextStatusFor((string) $this->status);
    }

    public static function nextStatusFor(string $status): ?string
    {
        return self::NEXT_STATUSES[$status] ?? null;
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeCancelledByStaff(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_PENDING,
                self::STATUS_CONFIRMED,
            ],
            true,
        );
    }

    public function assertOwnedByCurrentUser(): void
    {
        if ((string) $this->user_id !== (string) Yii::$app->user->id) {
            throw new ForbiddenHttpException(
                'You are not allowed to access this order.',
            );
        }
    }
}
