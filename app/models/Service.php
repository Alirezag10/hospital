<?php

namespace app\models;

class Service extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'services';
    }

    public function rules()
    {
        return [
            [['title'], 'trim', 'skipOnArray' => true],
            [['price'], 'filter', 'filter'=>[Patient::class,'normalizeDigits'], 'skipOnArray'=>true],
            [['active'], 'default', 'value' => 1],
            [['title', 'price'], 'required'],
            [['price'], 'integer', 'min' => 0, 'max' => 4294967295],
            [['active'], 'in', 'range' => [0, 1]],
            [['title'], 'string', 'max' => 150],
            [['title'], 'unique', 'message' => 'خدمتی با این عنوان قبلاً ثبت شده است.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'title' => 'عنوان خدمت',
            'price' => 'قیمت',
            'active' => 'فعال',
        ];
    }

    public function getAdmissionServices()
    {
        return $this->hasMany(
            AdmissionService::class,
            ['service_id' => 'id']
        );
    }
}
