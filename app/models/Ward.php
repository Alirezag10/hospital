<?php

namespace app\models;

use yii\db\ActiveRecord;

class Ward extends ActiveRecord
{
    public static function tableName()
    {
        return 'wards';
    }

    public function rules()
    {
        return [
            [['name', 'floor'], 'trim', 'skipOnArray' => true],
            [['floor'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 1],
            [['active'], 'in', 'range' => [0, 1]],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['floor'], 'string', 'max' => 50],
            [['name'], 'unique', 'message' => 'بخشی با این نام قبلاً ثبت شده است.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'name' => 'نام بخش',
            'floor' => 'طبقه',
            'active' => 'وضعیت پذیرش جدید',
            'created_at' => 'تاریخ ثبت',
        ];
    }
}
