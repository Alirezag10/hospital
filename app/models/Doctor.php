<?php

namespace app\models;

use yii\db\ActiveRecord;

class Doctor extends ActiveRecord
{
    public static function tableName()
    {
        return 'doctors';
    }

    public function rules()
    {
        return [
            [['name', 'specialty'], 'trim', 'skipOnArray' => true],
            [['mobile'], 'filter', 'filter' => [Patient::class, 'normalizeMobile'], 'skipOnArray' => true],
            [['mobile'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 1],
            [['active'], 'in', 'range' => [0, 1]],
            [['name', 'specialty'], 'required'],
            [['name', 'specialty'], 'string', 'max' => 100],
            [['mobile'], 'match', 'pattern' => Patient::MOBILE_PATTERN, 'message' => 'شماره موبایل ایران را با پیش‌شماره معتبر وارد کنید.'],
            [['name', 'specialty'], 'unique', 'targetAttribute' => ['name', 'specialty'], 'message' => 'این پزشک با همین تخصص قبلاً ثبت شده است.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'name' => 'نام پزشک',
            'specialty' => 'تخصص',
            'mobile' => 'موبایل',
            'active' => 'وضعیت پذیرش جدید',
            'created_at' => 'تاریخ ثبت',
        ];
    }

    public function getUser() { return $this->hasOne(User::class, ['doctor_id' => 'id']); }
}
