<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
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
 * @property int|null $confirmed_at
 * @property int|null $processing_at
 * @property int|null $ready_at
 * @property int|null $completed_at
 * @property OrderStatusHistory[] $statusHistory
 * @property User|null $createdBy
 * @property User|null $updatedBy
 */

class Order extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_WARNING_MINUTES = 15;
    public const PRIORITY_CRITICAL_MINUTES = 30;

    private const NEXT_STATUSES = [
        self::STATUS_PENDING => self::STATUS_CONFIRMED,
        self::STATUS_CONFIRMED => self::STATUS_PROCESSING,
        self::STATUS_PROCESSING => self::STATUS_READY_FOR_PICKUP,
        self::STATUS_READY_FOR_PICKUP => self::STATUS_COMPLETED,
    ];

    public static function statusValues(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_READY_FOR_PICKUP,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_READY_FOR_PICKUP => 'Ready for pickup',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public static function tableName(): string
    {
        return '{{%orders}}';
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
            [['user_id', 'order_number', 'status', 'total_amount'], 'required'],
            [['user_id'], 'integer'],
            [['order_number'], 'string', 'max' => 40],
            [['checkout_token'], 'string', 'max' => 64],
            [['order_number'], 'unique'],
            [['checkout_token'], 'unique', 'skipOnEmpty' => true],
            [['total_amount'], 'number', 'min' => 0],
            [['confirmed_at', 'processing_at', 'ready_at', 'completed_at'], 'integer'],
            [['confirmed_at', 'processing_at', 'ready_at', 'completed_at'], 'default', 'value' => null],
            [
                ['status'],
                'in',
                'range' => self::statusValues(),
            ],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCreatedBy(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
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

    public function isActiveForBoard(): bool
    {
        return in_array((string) $this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_READY_FOR_PICKUP,
        ], true);
    }

    public function ageMinutes(?int $now = null): int
    {
        $now ??= time();
        $createdAt = (int) $this->created_at;

        return max(0, (int) floor(($now - $createdAt) / 60));
    }

    public function queueElapsedSeconds(?int $now = null): ?int
    {
        $confirmedAt = $this->hasAttribute('confirmed_at') && $this->confirmed_at !== null
            ? (int) $this->confirmed_at
            : null;
        if ($confirmedAt === null) {
            return null;
        }

        return max(0, ($now ?? time()) - $confirmedAt);
    }

    public function priorityLevel(?int $now = null): string
    {
        if (!$this->isActiveForBoard()) {
            return 'normal';
        }
        $seconds = $this->queueElapsedSeconds($now);
        if ($seconds === null) {
            return 'normal';
        }
        if ($seconds >= self::PRIORITY_CRITICAL_MINUTES * 60) {
            return 'critical';
        }
        if ($seconds >= self::PRIORITY_WARNING_MINUTES * 60) {
            return 'warning';
        }

        return 'normal';
    }

    public function waitingSeconds(?int $now = null): ?int
    {
        $now ??= time();
        $confirmedAt = $this->hasAttribute('confirmed_at') && $this->confirmed_at !== null ? (int) $this->confirmed_at : null;
        if ($confirmedAt !== null) {
            return max(0, $confirmedAt - (int) $this->created_at);
        }
        if ((string) $this->status === self::STATUS_PENDING) {
            return max(0, $now - (int) $this->created_at);
        }

        return null;
    }

    public function preparationSeconds(?int $now = null): ?int
    {
        $now ??= time();
        $processingAt = $this->hasAttribute('processing_at') ? $this->processing_at : null;
        $confirmedAt = $this->hasAttribute('confirmed_at') ? $this->confirmed_at : null;
        $readyAt = $this->hasAttribute('ready_at') ? $this->ready_at : null;
        $completedAt = $this->hasAttribute('completed_at') ? $this->completed_at : null;
        $start = $processingAt !== null ? (int) $processingAt : ($confirmedAt !== null ? (int) $confirmedAt : null);
        if ($start === null) {
            return null;
        }
        $end = $readyAt !== null ? (int) $readyAt : ($completedAt !== null ? (int) $completedAt : $now);
        if ($end < $start) {
            return null;
        }

        return $end - $start;
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
