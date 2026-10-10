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
    // Mobile prefixes in Iran's CRA numbering plan (ITU, August 2026).
    public const MOBILE_PATTERN = '/^(?=[0-9]{11}$)09(?:0[0-5]|1[0-9]|2[0-3]|3[0-9]|9(?:[0-4]|5(?:10|50)|6|8(?:1|2|3[0-2]|88)|9(?:0[0-3]|1|21|3[0-4]|5|69|77|8|9)))[0-9]+$/D';

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
            [['national_code', 'birth_date'], 'filter', 'filter' => [self::class, 'normalizeDigits'], 'skipOnArray' => true],
            [['mobile'], 'filter', 'filter' => [self::class, 'normalizeMobile'], 'skipOnArray' => true],
            [['birth_date'], 'default', 'value' => null],
            [['first_name', 'last_name', 'national_code', 'mobile'], 'required'],
            [['birth_date'], 'date', 'format' => 'php:Y-m-d', 'strictDateFormat' => true,
                'max' => date('Y-m-d'), 'tooBig' => 'تاریخ تولد نمی‌تواند در آینده باشد.',
                'message' => 'تاریخ تولد معتبر از تقویم انتخاب کنید.'],
            [['first_name', 'last_name'], 'string', 'max' => 100],
            [['national_code', 'mobile'], 'string', 'max' => 20],
            [['national_code'], 'match', 'pattern' => '/^[0-9]{10}$/D', 'message' => 'کد ملی باید دقیقاً ۱۰ رقم باشد.'],
            [['national_code'], 'validateNationalCode'],
            [['mobile'], 'match', 'pattern' => self::MOBILE_PATTERN, 'message' => 'شماره موبایل ایران را با پیش‌شماره معتبر وارد کنید.'],
            [['national_code'], 'unique'],
        ];
    }

    public function validateNationalCode($attribute)
    {
        $code = $this->$attribute;
        if (!is_string($code) || !preg_match('/^[0-9]{10}$/D', $code)) {
            return; // The preceding format rule reports this error.
        }

        if (preg_match('/^([0-9])\\1{9}$/D', $code)) {
            $this->addError($attribute, 'کد ملی با رقم‌های یکسان معتبر نیست.');
            return;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $code[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $expected = $remainder < 2 ? $remainder : 11 - $remainder;
        if ((int) $code[9] !== $expected) {
            $this->addError($attribute, 'رقم کنترل کد ملی معتبر نیست.');
        }
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

    /** Store national and international input in one 09xxxxxxxxx format. */
    public static function normalizeMobile($value)
    {
        $value = self::normalizeDigits($value);
        if (!is_string($value)) {
            return $value;
        }

        $value = preg_replace('/[\s()\-]+/u', '', $value);
        if (str_starts_with($value, '+98')) {
            return '0' . substr($value, 3);
        }
        if (str_starts_with($value, '0098')) {
            return '0' . substr($value, 4);
        }
        return $value;
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

    public function getAdmissions()
    {
        return $this->hasMany(Admission::class, ['patient_id' => 'id']);
    }
}
