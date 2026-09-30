<?php

namespace app\models;

class Discharge extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'discharges';
    }

    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],

            [
                ['admission_id', 'discharge_date', 'total_amount'],
                'required',
            ],

            [['admission_id'], 'integer', 'min' => 1],
            [['total_amount'], 'integer', 'min' => 0],
            [['description'], 'string'],

            [
                ['admission_id'],
                'unique',
                'message' => 'برای این پذیرش قبلاً ترخیص ثبت شده است.',
            ],

            [
                ['admission_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Admission::class,
                'targetAttribute' => ['admission_id' => 'id'],
                'message' => 'پذیرش انتخاب‌شده پیدا نشد.',
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        foreach ($scenarios as $scenario => $attributes) {
            foreach ($attributes as $index => $attribute) {
                if (in_array(
                    $attribute,
                    ['total_amount', 'discharge_date'],
                    true
                )) {
                    $scenarios[$scenario][$index] = '!' . $attribute;
                }
            }
        }

        return $scenarios;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'admission_id' => 'پذیرش بیمار',
            'discharge_date' => 'تاریخ ترخیص',
            'description' => 'توضیحات',
            'total_amount' => 'مبلغ کل',
            'created_at' => 'تاریخ ثبت',
        ];
    }

    public function getAdmission()
    {
        return $this->hasOne(
            Admission::class,
            ['id' => 'admission_id']
        );
    }
}