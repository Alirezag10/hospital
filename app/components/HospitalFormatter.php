<?php
namespace app\components;

class HospitalFormatter extends \yii\i18n\Formatter
{
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
        return is_string($value) && str_starts_with($value, '0000-00-00');
    }
}
