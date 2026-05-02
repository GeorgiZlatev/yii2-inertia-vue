<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\{ActiveQuery, ActiveRecord};

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $is_completed
 * @property int $created_by
 * @property int|null $completed_at
 * @property int $created_at
 * @property int $updated_at
 * @property User $creator
 */
final class Todo extends ActiveRecord
{
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getCreator(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function rules(): array
    {
        return [
            [['title', 'description'], 'required'],
            ['title', 'string', 'max' => 255],
            ['description', 'string', 'max' => 5000],
            ['is_completed', 'boolean'],
            ['is_completed', 'default', 'value' => false],
            [['created_by', 'completed_at', 'created_at', 'updated_at'], 'integer'],
            [
                'created_by',
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['created_by' => 'id'],
            ],
        ];
    }

    public static function tableName(): string
    {
        return '{{%todo}}';
    }
}
