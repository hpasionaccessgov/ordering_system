<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Ledger row for earned, redeemed, and manually adjusted loyalty points.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $order_id
 * @property int $points signed: positive for EARN, negative for REDEEM
 * @property string $transaction_type
 * @property string|null $remarks
 * @property int $created_at
 */
class LoyaltyPointTransaction extends ActiveRecord
{
    public const TYPE_EARN = 'EARN';
    public const TYPE_REDEEM = 'REDEEM';
    public const TYPE_ADJUST = 'ADJUST';

    public static function tableName(): string
    {
        return '{{%loyalty_point_transaction}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_EARN => 'Points earned',
            self::TYPE_REDEEM => 'Reward redeemed',
            self::TYPE_ADJUST => 'Manual adjustment',
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'points', 'transaction_type'], 'required'],
            [['user_id', 'order_id', 'points'], 'integer'],
            [['points'], 'compare', 'compareValue' => 0, 'operator' => '!='],
            [['transaction_type'], 'string', 'max' => 10],
            [['transaction_type'], 'in', 'range' => array_keys(self::typeLabels())],
            [['remarks'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'Customer',
            'order_id' => 'Order',
            'points' => 'Points',
            'transaction_type' => 'Type',
            'remarks' => 'Remarks',
            'created_at' => 'Date',
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    public function getTypeLabel(): string
    {
        return self::typeLabels()[$this->transaction_type] ?? (string) $this->transaction_type;
    }
}
