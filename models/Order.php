<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\db\ActiveQuery;

/**
 * Class Order
 * @package app\models
 *
 * @property int $user_id
 * @property string $order_number
 * @property string|null $checkout_token
 * @property string $status
 * @property float $total_amount
 * @property OrderStatusHistory[] $statusHistory
 */

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

    public static function statusValues(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

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
            [['user_id', 'order_number', 'status', 'total_amount'], 'required'],
            [['user_id'], 'integer'],
            [['order_number'], 'string', 'max' => 40],
            [['checkout_token'], 'string', 'max' => 64],
            [['order_number'], 'unique'],
            [['checkout_token'], 'unique', 'skipOnEmpty' => true],
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

    public function getStatusHistory(): ActiveQuery
    {
        return $this->hasMany(OrderStatusHistory::class, ['order_id' => 'id'])
            ->with('changedBy')
            ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC]);
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
