<?php

declare(strict_types=1);

namespace app\models;

use yii\web\UploadedFile;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%products}}".
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property int $status
 * @property string|null $image_path
 * @property string|null $image_mime
 * @property string|null $image_name
 * @property bool $is_featured
 * @property Category $categoryRecord
 * @property User|null $createdBy
 * @property User|null $updatedBy
 */
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
            BlameableBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['name', 'category_id', 'price', 'status'], 'required'],
            [['description'], 'string'],
            [['category_id', 'status'], 'integer'],
            [
                ['category_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::class,
                'targetAttribute' => ['category_id' => 'id'],
                'message' => 'Select a valid category.',
            ],
            [['category_id'], 'validateCategoryStatus'],
            [['image_path'], 'string', 'max' => 255],
            [['image_mime'], 'string', 'max' => 100],
            [['image_name'], 'string', 'max' => 255],
            [['is_featured'], 'boolean'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => ['png', 'jpg', 'jpeg', 'webp'], 'maxSize' => 3 * 1024 * 1024],
            [['price'], 'number', 'min' => 0, 'max' => 99999999.99],
            [['price'], 'filter', 'filter' => static function ($value): string {
                return number_format((float) $value, 2, '.', '');
            }],
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

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'category_id' => 'Category',
        ]);
    }

    public function getCategoryRecord(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getCreatedBy(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getUpdatedBy(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public function validateCategoryStatus(string $attribute): void
    {
        if ($this->hasErrors($attribute) || $this->$attribute === null) {
            return;
        }

        $category = Category::findOne((int) $this->$attribute);
        if ($category === null) {
            return;
        }

        $categoryWasUnchanged = !$this->isNewRecord
            && (int) $this->getOldAttribute($attribute) === (int) $this->$attribute;

        if ((int) $category->status !== Category::STATUS_ACTIVE && !$categoryWasUnchanged) {
            $this->addError($attribute, 'Select an active category.');
        }
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
