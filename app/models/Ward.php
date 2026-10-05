<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "wards".
 *
 * @property int $id
 * @property string $name
 * @property string|null $floor
 * @property int|null $capacity
 * @property string $created_at
 */
class Ward extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wards';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['floor'], 'default', 'value' => null],
            [['capacity'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['capacity'], 'integer', 'min' => 0],
            [['name'], 'string', 'max' => 100],
            [['floor'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'name' => 'نام بخش',
            'floor' => 'طبقه',
            'capacity' => 'ظرفیت',
            'created_at' => 'تاریخ ثبت',
        ];
    }

}
