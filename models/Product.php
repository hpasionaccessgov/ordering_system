<?php

declare(strict_types=1);

namespace app\models;

use yii\web\UploadedFile;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Url;

class Product extends ActiveRecord
{
    public ?UploadedFile $imageFile = null;
    public const STATUS_ARCHIVED = 0;
    public const STATUS_ACTIVE = 10;
    public static function tableName(): string
    {
        return '{{%products}}';
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
            [['name', 'price', 'status'], 'required'],
            [['description'], 'string'],
            [['category'], 'string', 'max' => 80],
            [['image_path'], 'string', 'max' => 255],
            [['image_mime'], 'string', 'max' => 100],
            [['image_name'], 'string', 'max' => 255],
            [['is_featured'], 'boolean'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['png', 'jpg', 'jpeg', 'webp'], 'maxSize' => 3 * 1024 * 1024],
            [['price'], 'number', 'min' => 0, 'max' => 99999999.99],
            [['price'], 'filter', 'filter' => static function ($value): string {
                return number_format((float) $value, 2, '.', '');
            }],
            [['status'], 'integer'],
            [
                ['status'],
                'in',
                'range' => [
                    self::STATUS_ACTIVE,
                    self::STATUS_ARCHIVED,
                ],
            ],
            [['name'], 'string', 'max' => 150],
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function getImageUrl(): ?string
    {
        if ($this->image_data !== null && $this->image_data !== '') {
            return Url::to(['/product/image', 'id' => $this->id]);
        }

        if ($this->image_path) {
            return \Yii::getAlias('@web/' . ltrim($this->image_path, '/'));
        }

        return null;
    }
}
