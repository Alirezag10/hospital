<?php

namespace app\models;

/**
 * Model for the "admission_services" table.
 *
 * @property int $id
 * @property int $admission_id
 * @property int $service_id
 * @property int $quantity
 * @property int $unit_price
 * @property string $created_at
 *
 * @property Admission $admission
 * @property Service $service
 */
class AdmissionService extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'admission_services';
    }

    public function rules()
    {
        return [
            [['quantity'], 'default', 'value' => 1],

            [
                ['admission_id', 'service_id', 'quantity', 'unit_price'],
                'required',
            ],

            [
                ['admission_id', 'service_id'],
                'integer',
                'min' => 1,
            ],

            [['quantity'], 'integer', 'min' => 1],
            [['unit_price'], 'integer', 'min' => 0],

            [
                ['admission_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Admission::class,
                'targetAttribute' => ['admission_id' => 'id'],
                'filter' => ['status' => 'admitted'],
                'message' => 'یک پذیرش بستری انتخاب کنید.',
            ],

            [
                ['service_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Service::class,
                'targetAttribute' => ['service_id' => 'id'],
                'filter' => ['active' => 1],
                'message' => 'یک خدمت فعال انتخاب کنید.',
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        foreach ($scenarios as $scenario => $attributes) {
            foreach ($attributes as $index => $attribute) {
                if ($attribute === 'unit_price') {
                    // قیمت اعتبارسنجی می‌شود، اما از فرم دریافت نمی‌شود.
                    $scenarios[$scenario][$index] = '!unit_price';
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
            'service_id' => 'خدمت',
            'quantity' => 'تعداد',
            'unit_price' => 'قیمت واحد',
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

    public function getService()
    {
        return $this->hasOne(
            Service::class,
            ['id' => 'service_id']
        );
    }
}