<?php
namespace app\models;

class MedicalItem extends \yii\db\ActiveRecord
{
    public static function tableName() { return 'medical_items'; }
    public function rules()
    {
        return [
            [['title'], 'trim', 'skipOnArray' => true],
            [['kind','title','unit_price'], 'required'],
            [['kind'], 'in', 'range' => ['medicine','supply']],
            [['title'], 'string', 'max' => 150],
            [['unit_price'], 'filter', 'filter' => [Patient::class,'normalizeDigits'], 'skipOnArray' => true],
            [['unit_price'], 'integer', 'min' => 0, 'max' => 4294967295],
            [['active'], 'default', 'value' => 1],
            [['active'], 'in', 'range' => [0,1]],
            [['title'], 'unique', 'targetAttribute' => ['kind','title'], 'message' => 'این قلم از قبل در فهرست وجود دارد.'],
        ];
    }
    public function attributeLabels()
    {
        return ['kind'=>'نوع قلم','title'=>'نام دارو یا وسیله','unit_price'=>'قیمت واحد (تومان)','active'=>'وضعیت'];
    }
}
