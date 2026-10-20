<?php
namespace app\models;

class PrescriptionItem extends \yii\db\ActiveRecord
{
    public static function tableName() { return 'prescription_items'; }
    public static function kindLabels(): array { return ['medicine' => 'دارو', 'supply' => 'لوازم مصرفی']; }
    public function rules()
    {
        return [
            [['title', 'dosage', 'instructions'], 'trim', 'skipOnArray' => true],
            [['quantity', 'unit_price'], 'filter', 'filter' => [Patient::class, 'normalizeDigits'], 'skipOnArray' => true],
            [['kind', 'title', 'quantity', 'unit_price'], 'required'],
            [['kind'], 'in', 'range' => array_keys(self::kindLabels())],
            [['title'], 'string', 'max' => 150],
            [['dosage'], 'string', 'max' => 100],
            [['instructions'], 'string', 'max' => 500],
            [['quantity'], 'integer', 'min' => 1, 'max' => 4294967295],
            [['unit_price'], 'integer', 'min' => 0, 'max' => 4294967295],
        ];
    }
    public function attributeLabels()
    {
        return ['kind' => 'نوع قلم', 'title' => 'نام دارو یا وسیله', 'quantity' => 'تعداد',
            'unit_price' => 'قیمت واحد (تومان)', 'dosage' => 'مقدار و دفعات مصرف', 'instructions' => 'دستور مصرف یا توضیح'];
    }
}
