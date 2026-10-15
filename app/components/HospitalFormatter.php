<?php
namespace app\components;

class HospitalFormatter extends \yii\i18n\Formatter
{
    public function asInteger($value, $options = [], $textOptions = [])
    {
        return strtr(parent::asInteger($value, $options, $textOptions), [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    public function asDate($value, $format = null)
    {
        return $this->isZeroDate($value) ? 'نامشخص' : parent::asDate($value, $format);
    }

    public function asDatetime($value, $format = null)
    {
        return $this->isZeroDate($value) ? 'نامشخص' : parent::asDatetime($value, $format);
    }

    private function isZeroDate($value): bool
    {
        return $value === null || $value === '' || (is_string($value) && str_starts_with($value, '0000-00-00'));
    }
}
