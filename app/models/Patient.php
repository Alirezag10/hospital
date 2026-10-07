<?php

namespace app\models;

/**
 * This is the model class for table "patients".
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $national_code
 * @property string $mobile
 * @property string|null $birth_date
 * @property string $created_at
 */
class Patient extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'patients';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name'], 'trim', 'skipOnArray' => true],
            [['national_code', 'mobile', 'birth_date'], 'filter', 'filter' => [self::class, 'normalizeDigits'], 'skipOnArray' => true],
            [['birth_date'], 'default', 'value' => null],
            [['first_name', 'last_name', 'national_code', 'mobile'], 'required'],
            [['birth_date'], 'date', 'format' => 'php:Y-m-d', 'strictDateFormat' => true,
                'max' => date('Y-m-d'), 'tooBig' => 'تاریخ تولد نمی‌تواند در آینده باشد.',
                'message' => 'تاریخ تولد معتبر از تقویم انتخاب کنید.'],
            [['first_name', 'last_name'], 'string', 'max' => 100],
            [['national_code', 'mobile'], 'string', 'max' => 20],
            [['national_code'], 'match', 'pattern' => '/^[0-9]{10}$/D', 'message' => 'کد ملی باید دقیقاً ۱۰ رقم باشد.'],
            [['mobile'], 'match', 'pattern' => '/^09[0-9]{9}$/D', 'message' => 'موبایل باید ۱۱ رقم باشد و با ۰۹ شروع شود.'],
            [['national_code'], 'unique'],
        ];
    }

    /** Keep identifiers as strings so their leading zeros are preserved. */
    public static function normalizeDigits($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        return strtr(trim($value), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'national_code' => 'کد ملی',
            'mobile' => 'موبایل',
            'birth_date' => 'تاریخ تولد',
            'created_at' => 'تاریخ ثبت',
        ];
    }
}
