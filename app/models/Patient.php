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
            [['birth_date'], 'default', 'value' => null],
            [['first_name', 'last_name', 'national_code', 'mobile'], 'required'],
            [['birth_date'], 'safe'],
            [['first_name', 'last_name'], 'string', 'max' => 100],
            [['national_code', 'mobile'], 'string', 'max' => 20],
            [['national_code'], 'unique'],
        ];
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