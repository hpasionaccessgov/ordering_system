<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $order_id
 * @property int $rating
 * @property string|null $review
 * @property int $created_at
 *
 * @property-read Order $order
 * @property-read Product $product
 * @property-read User $user
 */
class ProductReview extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%product_review}}';
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

    public function rules(): array
    {
        return [
            [['user_id', 'product_id', 'order_id', 'rating', 'review'], 'required'],
            [['user_id', 'product_id', 'order_id'], 'integer'],
            [['rating'], 'integer', 'min' => 1, 'max' => 5],
            [['review'], 'string', 'max' => 1000],
            [['created_at'], 'integer'],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id'],
            ],
            [
                ['product_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Product::class,
                'targetAttribute' => ['product_id' => 'id'],
            ],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Order::class,
                'targetAttribute' => ['order_id' => 'id'],
            ],
            [
                ['order_id'],
                'unique',
                'targetAttribute' => ['user_id', 'product_id', 'order_id'],
                'message' => 'You have already reviewed this product from this order.',
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'product_id' => 'Product',
            'order_id' => 'Order',
            'rating' => 'Rating',
            'review' => 'Review',
            'created_at' => 'Reviewed at',
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getProduct(): ActiveQuery
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }
}
